<?php
define('BX_SESSION_ID_CHANGE', false);
define('BX_SKIP_POST_UNQUOTE', true);
define('NO_AGENT_CHECK', true);
define('STATISTIC_SKIP_ACTIVITY_CHECK', true);
define('BX_FORCE_DISABLE_SEPARATED_SESSION_MODE', true);

use Bitrix\Main\Localization\Loc;

/**
 * Функция первого шага - загружает файл в Битрикс, для дальнейшго использования на втором шаге
 */
function uploadFile(array $file)
{
    if ($file['error'] == UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileName          = $file['name'];
        $fileExtension     = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $allowedExtensions)) {
            return 'Error: недопустимое расширение файла.';
        } else {
            $offerKey = pathinfo($fileName, PATHINFO_FILENAME);
            $arFilter = ['IBLOCK_ID' => 49, 'XML_ID' => $offerKey];
            $rsOffer  = \CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID', 'PROPERTY_BASIC_CONFIGURATION', 'PROPERTY_CML2_LINK']);

            if (!$rsOffer->Fetch()) {
                return 'Error: товар не найден';
            }

            $fileArray = [
                'name'      => $fileName,
                'size'      => $file['size'],
                'tmp_name'  => $file['tmp_name'],
                'type'      => $file['type'],
                'MODULE_ID' => 'main',
            ];

            $fileId = CFile::SaveFile($fileArray, 'upload');
            if ($fileId <= 0) {
                return 'Error: ошибка при сохранении файла.';
            }

            return (int) $fileId;
        }
    } else {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Error: Размер файла превышает допустимый размер, указанный в php.ini.';

                break;
            case UPLOAD_ERR_FORM_SIZE:
                return 'Error: Размер файла превышает допустимый размер, указанный в форме.';

                break;
            case UPLOAD_ERR_PARTIAL:
                return 'Error: Файл был загружен только частично.';

                break;
            case UPLOAD_ERR_NO_FILE:
                return 'Error: Файл не был загружен.';

                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Error: Отсутствует временная папка.';

                break;
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error: Не удалось записать файл на диск.';

                break;
            case UPLOAD_ERR_EXTENSION:
                return 'Error: Загрузка файла была остановлена расширением PHP.';

                break;
            default:
                return 'Error: Неизвестная ошибка при загрузке файла.';

                break;
        }
    }
}

/**
 * Функция второго шага - обновляет файл у торгового предложения (и элемента, если базавый).
 */
function updateElement(string $fileName)
{
    if (empty($fileName)) {
        return 'Error: Имя файла не указано.';
    }

    $rsFile = \CFile::GetList(['ID' => 'desc'], ['ORIGINAL_NAME' => $fileName]);
    $file   = $rsFile->Fetch();

    if (!$file) {
        return 'Error: Файл не найден.';
    }

    $error    = '';
    $offerKey = pathinfo($fileName, PATHINFO_FILENAME);

    if (!empty($offerKey) && !empty($file['ID'])) {
        $arFilter = ['IBLOCK_ID' => 49, 'XML_ID' => $offerKey];
        $rsOffer  = \CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID', 'PROPERTY_BASIC_CONFIGURATION', 'PROPERTY_CML2_LINK']);

        if ($arOffer = $rsOffer->Fetch()) {
            $arElement = [];
            $of        = new \CIBlockElement();
            $fileArray = \CFile::MakeFileArray(\CFile::GetPath($file['ID']));

            $arFields['DETAIL_PICTURE']         = $fileArray;
            $arFields['PREVIEW_PICTURE']        = $fileArray;
            $arFields['DETAIL_PICTURE']['del']  = 'Y';
            $arFields['PREVIEW_PICTURE']['del'] = 'Y';

            if ($of->Update(intval($arOffer['ID']), $arFields)) {
                if ($arOffer['PROPERTY_BASIC_CONFIGURATION_VALUE'] === 'Да') {
                    $rsElement  = \CIBlockElement::GetList([], ['IBLOCK_ID' => 48, 'ID' => intval($arOffer['PROPERTY_CML2_LINK_VALUE'])]);
                    if ($arElement = $rsElement->Fetch()) {
                        $arElement['DETAIL_PICTURE']         = $fileArray;
                        $arElement['PREVIEW_PICTURE']        = $fileArray;
                        $arElement['DETAIL_PICTURE']['del']  = 'Y';
                        $arElement['PREVIEW_PICTURE']['del'] = 'Y';

                        $el = new \CIBlockElement();
                        if (!$el->Update(intval($arElement['ID']), $arElement)) {
                            $error =  'Error Update element ' . $el->LAST_ERROR;
                        }
                    } else {
                        $error =  'Error Element Fetch() ';
                    }
                }
            } else {
                $error = 'Error Update offer ' . $of->LAST_ERROR;
            }
        } else {
            $error =  "Товар {$offerKey} не найден";
        }
    } else {
        $error =  'Error $offerKey: ' . $offerKey;
    }

    CFile::Delete($file['ID']);

    if ($error !== '') {
        return $error;
    }

    return true;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iblock/prolog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/prolog.php';

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('catalog')) {
    die('Необходимые модули не загружены');
}

$rights = $APPLICATION->GetGroupRight('catalog');
if ($rights == 'D') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if ($request->isAjaxRequest() && $request->isAdminSection()) {
    if ($request->getPost('undo') === 'Y') {
        if ($fileId = $request->getPost('file')) {
            echo json_encode([
                'success' => true,
                'result'  => CFile::Delete($fileId)
            ]);
            die();
        }
    }

    $stage       = (int) $request->getPost('stage');
    $filesValues = $request->getFileList()->toArray();

    if ($stage === 0) {
        if (!empty($filesValues['file'])) {
            $result = uploadFile($filesValues['file']);
        } else {
            echo json_encode([
                'success' => false,
                'result'  => 'Нужно загрузить хотябы один файл'
            ]);
            die();
        }
    } elseif ($stage === 1) {
        $result = updateElement($request->getPost('file'));
    }

    if ($result === true) {
        echo json_encode([
            'success' => true,
            'result'  => $result
        ]);
    } elseif (is_int($result)) {
        echo json_encode([
            'success' => true,
            'result'  => ['id' => $result]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'result'  => 'Не удалось обновить файл. ' . $result
        ]);
    }
    die();
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$APPLICATION->SetTitle('Загрузка картинок в каталог');

\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
\Bitrix\Main\UI\Extension::load('ui.notification');
\Bitrix\Main\UI\Extension::load('ui.bootstrap4');

?>
<form method="POST" action="<?php echo $request->getRequestUri() ?>" ENCTYPE="multipart/form-data" name="post_files_form">
    <input type="hidden" name="stage" value="0">
    <?= bitrix_sessid_post(); ?>
    <div class="drop-container">
        <?php
        echo BeginNote();
?>

        <div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon">
            Перетащите и бросьте в это окно папки с файлами
        </div>

        <?php
echo EndNote();
?>
    </div>

    <div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon" id="btn-container">
        <button class="ui-btn ui-btn-icon-add ui-btn-primary" onclick="submitFiles();" disabled
            name="save">Отправить</button>

        <button class="ui-btn ui-btn-icon-add ui-btn-primary" onclick="undoUpload();" disabled name="undo">Отменить
            загрузку</button>
    </div>
</form>

<script>
    var         files = []; // Объявляем массив глобальным, т.к. нет состояний приложения и чтобы не передавать его из функции в функцию по 100 раз через задний проход. 
    var uploadedFiles = []; // Файлы, загруженные на сервер на первом шаге

    function submitFiles() {
        this.event.preventDefault();
        const form = document.querySelector('form[name="post_files_form"]');
        const stage = form.querySelector('input[name="stage"]');
        const url = form.getAttribute("action");
        const data = new FormData(form);

        if (files.length > 0 && stage.value === "0") {
            BX.UI.Notification.Center.notify({
                content: "Начат процесс загрузки. Пожалуйста, дождитесь уведомления об окончании процесса.",
                position: "top-center",
                autoHideDelay: 5000,
                closeButton: true,
                category: "form-action",
            });

            uploadedFiles = []; // лучше перебздеть, чем недобздеть

            for (const file of files) {
                data.set('file', file);

                BX.ajax({
                    url: url,
                    data: data,
                    method: 'POST',
                    dataType: 'json',
                    processData: false,
                    preparePost: false,
                    onsuccess: function(data) {
                        data = JSON.parse(data);
                        const li = Array.prototype.slice.call(form.querySelectorAll('li'))
                            .filter(function(el) {
                                return el.textContent === file.name
                            })[0];

                        if (data.success) {
                            uploadedFiles.push({
                                id: data.result.id,
                                name: file.name
                            });

                            li.insertAdjacentHTML('beforeend',
                                '<span class="badge rounded-pill bg-success">Загружен</span>');
                        } else {
                            console.error(data.result);
                            li.insertAdjacentHTML('beforeend',
                                '<span class="badge rounded-pill bg-danger">' + data.result + '</span>');
                        }
                    },
                    onfailure: function(data) {
                        console.error(data)
                    }
                });
            }
            stage.value = "1";
            const btnSubmint = form.querySelector('[name="save"]');
            btnSubmint.innerText = 'Сохранить загруженные файлы';

            const btnUndo = form.querySelector('[name="undo"]');
            btnUndo.disabled = false;

            const title = form.querySelector('.drop-container h5');
            title.innerText = '';
        } else if (stage.value === "1") {
            const title = form.querySelector('.drop-container h5');
            title.innerText = 'Сохраненные файлы:';

            BX.UI.Notification.Center.notify({
                content: "Сохраняем файлы",
                position: "top-center",
                autoHideDelay: 5000,
                closeButton: true,
                category: "form-action",
            });

            const ulElement = form.querySelector('ul');

            for (const file of uploadedFiles) {
                data.set('file', file.name);

                BX.ajax({
                    url: url,
                    data: data,
                    method: 'POST',
                    dataType: 'json',
                    processData: false,
                    preparePost: false,
                    onsuccess: function(data) {
                        data = JSON.parse(data);
                        const liElement = document.createElement('li');
                        liElement.classList.add('list-group-item');
                        liElement.classList.add('d-flex');
                        liElement.classList.add('justify-content-between');
                        liElement.classList.add('align-items-center');
                        liElement.classList.add('bg-transparent');
                        if (data.success) {
                            console.log(data.result);
                            liElement.innerHTML = file.name +
                                ' <span class="badge rounded-pill bg-success">Обновлен</span>';
                        } else {
                            liElement.innerHTML = file.name +
                                ' <span class="badge rounded-pill bg-danger">' + data.result + '</span>';
                            console.error(data.result);
                        }
                        ulElement.appendChild(liElement);
                    },
                    onfailure: function(data) {
                        console.error(data)
                    }
                });
            }

            stage.value = "2";
            const btnContainer = form.querySelector('#btn-container');
            btnContainer.parentNode.removeChild(btnContainer);
        }
    }

    function undoUpload() {
        this.event.preventDefault();

        const form = document.querySelector('form[name="post_files_form"]');
        const url = form.getAttribute("action");
        const data = new FormData(form);
        data.set('undo', "Y");

        const ulElement = form.querySelector('ul');
        ulElement.innerHTML = '';

        for (const file of uploadedFiles) {
            console.log(file);

            data.set('file', file.id);

            BX.ajax({
                url: url,
                data: data,
                method: 'POST',
                dataType: 'json',
                processData: false,
                preparePost: false,
                onsuccess: function(data) {
                    data = JSON.parse(data);
                    const liElement = document.createElement('li');
                    liElement.classList.add('list-group-item');
                    liElement.classList.add('d-flex');
                    liElement.classList.add('justify-content-between');
                    liElement.classList.add('align-items-center');
                    liElement.classList.add('bg-transparent');
                    if (data.success) {
                        liElement.innerHTML = file.name +
                            ' <span class="badge rounded-pill bg-success">Удален</span>';
                    } else {
                        liElement.innerHTML = file.name + ' <span class="badge rounded-pill bg-danger">' +
                            data.result + '</span>';
                        console.error(data.result);
                    }
                    ulElement.appendChild(liElement);
                },
                onfailure: function(data) {
                    console.error(data)
                }
            });
        }

        const btnContainer = form.querySelector('#btn-container');
        btnContainer.parentNode.removeChild(btnContainer);
    }

    const placeholder = document.querySelector('.drop-container');
    placeholder.addEventListener('dragover', dragover);
    placeholder.addEventListener('dragenter', dragenter);
    placeholder.addEventListener('dragleave', dragleave);
    placeholder.addEventListener('drop', dragdrop);


    function dragover(event) {
        event.preventDefault();
    }

    function dragenter(event) {
        event.target.classList.add('hovered');
    }

    function dragleave(event) {
        event.target.classList.remove('hovered');
    }

    function sleep(milliseconds) {
        return new Promise(resolve => setTimeout(resolve, milliseconds));
    }

    async function dragdrop(event) {
        event.preventDefault();
        event.target.classList.remove('hovered');
        event.target.innerHTML = '<h5>Файлы для загрузки:</h5>';

        files = getFiles(event.dataTransfer);

        await sleep(500);

        const ulElement = document.createElement('ul');
        ulElement.classList.add('list-group');
        ulElement.classList.add('list-group-flush');

        for (const file of files) {
            const liElement = document.createElement('li');
            liElement.classList.add('list-group-item');
            liElement.classList.add('d-flex');
            liElement.classList.add('justify-content-between');
            liElement.classList.add('align-items-center');
            liElement.classList.add('bg-transparent');
            liElement.innerHTML = file.name;
            ulElement.appendChild(liElement);
        }
        event.target.appendChild(ulElement);

        const form = document.querySelector('form[name="post_files_form"]');
        form.querySelector('input[name="stage"]').value = "0";

        const btnSubmint = form.querySelector('[name="save"]');
        btnSubmint.disabled = false;
    }

    function getFiles(dataTranfer) {
        const files = [];
        for (let i = 0; i < dataTranfer.items.length; i++) {
            const item = dataTranfer.items[i];
            if (item.kind === 'file') {
                if (typeof item.webkitGetAsEntry === 'function') {
                    const entry = item.webkitGetAsEntry();
                    readEntryContent(entry);
                    continue;
                }

                const file = item.getAsFile();
                if (file) {
                    files.push(file);
                }
            }
        }
        return files;

        function readEntryContent(entry) {
            readEntry(entry);

            function readEntry(entry) {
                if (entry.isFile) {
                    entry.file((file) => {
                        files.push(file);
                    });
                } else if (entry.isDirectory) {
                    readReaderContent(entry.createReader());
                }
            };

            function readReaderContent(reader) {
                reader.readEntries(function(entries) {
                    for (const entry of entries) {
                        readEntry(entry);
                    }
                });
            };
        };
    };
</script>

<style>
    .drop-container {
        width: 100%;
        min-height: 400px;
        border: 3px solid;
        border-radius: 10px;
        padding: 5px;
        margin-bottom: 5px;
    }

    .hide {
        display: none;
    }

    .hovered {
        border: 3px solid #828282;
        transition: all ease-out 0.3s;
        border-radius: 10px;
        background: linear-gradient(90deg, #eee 0%, #828282 200%);
        flex: 20%;
    }
</style>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
?>