<?php

namespace Absteam\SpeedMeter;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use PDO;
use PDOStatement;

class MonologHandler extends AbstractProcessingHandler
{
    /**
     * @var bool определяет, инициализировано ли соединение с MySQL
     */
    private $initialized = false;

    /**
     * @var PDO объект соединения с базой данных
     */
    protected $pdo;

    /**
     * @var PDOStatement подготовленный запрос для вставки новой записи
     */
    private $statement;

    /**
     * @var string таблица для хранения логов
     */
    private $table = 'logs';

    /**
     * @var array поля по умолчанию, которые хранятся в базе данных
     */
    private $defaultfields = array('id', 'channel', 'level', 'message', 'time');

    /**
     * @var string[] дополнительные поля для хранения в базе данных
     *
     * Для каждого поля $field ожидается дополнительное поле контекста с именем $field
     * вместе с сообщением, и далее база данных должна иметь эти поля,
     * так как значения хранятся в столбце с именем $field.
     */
    private $additionalFields = array();

    /**
     * @var array
     */
    private $fields = array();

    /**
     * Конструктор этого класса, устанавливает PDO и вызывает родительский конструктор
     *
     * @param PDO $pdo Соединение с базой данных PDO
     * @param bool $table Таблица в базе данных для хранения логов
     * @param array $additionalFields Дополнительные параметры контекста для хранения в базе данных
     * @param bool|int $level Уровень отладки, который должен обрабатывать этот обработчик
     * @param bool $bubble
     * @param bool $skipDatabaseModifications Определяет, следует ли пропускать попытки изменения базы данных
     */
    public function __construct(
        PDO $pdo = null,
        $table,
        $additionalFields = array(),
        $level = \Monolog\Level::Debug,
        $bubble = true,
        $skipDatabaseModifications = false
    ) {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        }
        $this->table            = $table;
        $this->additionalFields = $additionalFields;
        parent::__construct($level, $bubble);

        if ($skipDatabaseModifications) {
            $this->mergeDefaultAndAdditionalFields();
            $this->initialized = true;
        }
    }

    /**
     * Инициализирует этот обработчик, создавая таблицу, если она не существует
     */
    private function initialize()
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `' . $this->table . '` '
                . '(id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, channel VARCHAR(255), level INTEGER, message LONGTEXT, time INTEGER UNSIGNED, INDEX(channel) USING HASH, INDEX(level) USING HASH, INDEX(time) USING BTREE)'
        );

        //Считываем фактические столбцы
        $actualFields = array();
        $rs           = $this->pdo->query('SELECT * FROM `' . $this->table . '` LIMIT 0');
        for ($i = 0; $i < $rs->columnCount(); $i++) {
            $col            = $rs->getColumnMeta($i);
            $actualFields[] = $col['name'];
        }

        //Вычисляем измененные записи
        $removedColumns = array_diff(
            $actualFields,
            $this->additionalFields,
            $this->defaultfields
        );
        $addedColumns = array_diff($this->additionalFields, $actualFields);

        //Удаляем столбцы
        if (!empty($removedColumns)) {
            foreach ($removedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `' . $this->table . '` DROP `' . $c . '`;');
            }
        }

        //Добавляем столбцы
        if (!empty($addedColumns)) {
            foreach ($addedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `' . $this->table . '` add `' . $c . '` TEXT NULL DEFAULT NULL;');
            }
        }

        $this->mergeDefaultAndAdditionalFields();

        $this->initialized = true;
    }

    /**
     * Подготавливает SQL-запрос в зависимости от полей, которые должны быть записаны в базу данных
     */
    private function prepareStatement()
    {
        //Подготовка запроса
        $columns = "";
        $fields  = "";
        foreach ($this->fields as $key => $f) {
            if ($f == 'id') {
                continue;
            }
            if ($key == 1) {
                $columns .= "$f";
                $fields .= ":$f";
                continue;
            }

            $columns .= ", $f";
            $fields .= ", :$f";
        }

        $this->statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->table . '` (' . $columns . ') VALUES (' . $fields . ')'
        );
    }

    /**
     * Записывает запись в лог, реализуя обработчик
     *
     * @param  $record []
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        /**
         * сбрасываем $fields с значениями по умолчанию
         */
        $this->fields = $this->defaultfields;
        $record       = $record->toArray();

        /*
         * объединяем $record['context'] и $record['extra'] как дополнительную информацию от Processors
         * добавляемую в $record['extra']
         * @see https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md
         */
        if (isset($record['extra'])) {
            $record['context'] = array_merge($record['context'], $record['extra']);
        }

        //'context' содержит массив
        $contentArray = array_merge(array(
            'channel' => $record['channel'],
            'level'   => $record['level'],
            'message' => $record['message'],
            'time'    => $record['datetime']->format('Y-m-d H:i:s')
        ), $record['context']);

        // удаляем ключи массива, которые переданы, но не определены для хранения, чтобы предотвратить ошибки SQL
        foreach ($contentArray as $key => $context) {
            if (!in_array($key, $this->fields)) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields)]);
                continue;
            }

            if ($context === null) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields)]);
            }
        }

        $this->prepareStatement();

        //Удаляем неиспользуемые ключи
        foreach ($this->additionalFields as $key => $context) {
            if (!isset($contentArray[$key])) {
                unset($this->additionalFields[$key]);
            }
        }

        //Заполняем массив содержимого значениями "null", если они не предоставлены
        $contentArray = $contentArray + array_combine(
            $this->additionalFields,
            array_fill(0, count($this->additionalFields), null)
        );

        $this->statement->execute($contentArray);
    }

    /**
     * Объединяет поля по умолчанию и дополнительные поля в один массив
     */
    private function mergeDefaultAndAdditionalFields()
    {
        $this->defaultfields = array_merge($this->defaultfields, $this->additionalFields);
    }
}
