/*
You can use this file with your scripts.
It will not be overwritten when you upgrade solution.
*/

$(document).on('click', '.scroll-to-item', function () {
    var id = $(this).attr('data-id');
    var indent = $(this).attr('data-indent');

    if (id !== undefined) {
        if (id == 1) {
            var diff = indent !== undefined ? indent : 25;
        } else {
            var diff = indent !== undefined ? indent : $('header').height();
        }

        var ofcetTop = $('.s-section-' + id).offset().top - diff;
        smoothScrollTo(ofcetTop);
    }
});

$('.search-block-btn').on('click', function () {
    $(this).closest('.search-block').toggleClass('_show-search');
});

window.smoothScrollTo = (function () {
    var timer, start, factor;

    return function (target, duration) {
        var offset = window.pageYOffset,
            delta = target - window.pageYOffset;
        duration = duration || 1000;
        start = Date.now();
        factor = 0;

        if (timer) {
            clearInterval(timer);
        }

        function step() {
            var y;
            factor = (Date.now() - start) / duration;
            if (factor >= 1) {
                clearInterval(timer);
                factor = 1;
            }
            y = factor * delta + offset;
            window.scrollBy(0, y - window.pageYOffset);
        }

        timer = setInterval(step, 10);
        return timer;
    };
})();

$(document).ready(function () {
    Inputmask.extendDefinitions({
        //n - nine
        n: {
            validator: '[9]',
        },
    });
    var eventSend = false;
    $('.contacts-page-top .ik_select.common_select').on('click', function () {
        if (!eventSend) {
            dataLayer.push({
                event: 'eventTarget',
                eventCategory: 'target',
                eventAction: '02_intr_click_geo_on_top',
                eventLabel: '',
            });
            eventSend = true;
        }
    });

    $('.mobile_regions .mobile_regions_city_item').on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        $.removeCookie('current_region');
        $.cookie('current_region', _this.attr('data-id'), { path: '/', domain: arAsproOptions['SITE_ADDRESS'] });
        location.href = _this.attr('data-href');
    });

    fullWindowWidthBlockHandler();

    if (document.getElementById('map')) yandexMapDeliveryLoad('map');
});

$(window).resize(function () {
    fullWindowWidthBlockHandler();
});

$(document).on('change', '.uploader input[type=file]', function () {
    var val = $(this).val();
    var name = $(this).attr('name');
    var multiple = $(this).attr('data-type');
    if (val != '' && typeof multiple !== 'undefined') {
        var addInput = true;
        $(this)
            .parents('.form')
            .find('.uploader')
            .each(function (index, el) {
                if ($(el).children('.inputfile').val() == '') {
                    addInput = false;
                }
            });

        if (addInput) {
            var inputHtml = '';
            inputHtml += '<input data-sid="FILE" name="' + name + '" class="inputfile" type="file">';
            $(this).parents('.uploader').after(inputHtml);
            $(this)
                .parents('.form')
                .find('input[type=file]:last')
                .uniform({
                    fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'),
                    fileDefaultHtml: BX.message('JS_FILE_DEFAULT'),
                });
        }
    }
    if (!$(this).next().length || !$(this).next().hasClass('resetfile')) {
        $(
            '<span class="resetfile"><svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11"><path d="M1345.19,376.484l4.66,4.659a0.492,0.492,0,0,1,0,.707,0.5,0.5,0,0,1-.71,0l-4.66-4.659-4.65,4.659a0.5,0.5,0,0,1-.71,0,0.492,0.492,0,0,1,0-.707l4.66-4.659-4.66-4.659a0.492,0.492,0,0,1,0-.707,0.5,0.5,0,0,1,.71,0l4.65,4.659,4.66-4.659a0.5,0.5,0,0,1,.71,0,0.492,0.492,0,0,1,0,.707Z" transform="translate(-1339 -371)"/></svg></span>'
        ).insertAfter($(this));
    }
});

function fullWindowWidthBlockHandler() {
    if ($('div').is('.full-window-width-block')) {
        var windowWidth = $(window).width();
        $('.full-window-width-block').css({ width: 'auto', left: 'auto' });
        $('.full-window-width-block').css({
            width: windowWidth,
            left: '-' + $('.full-window-width-block').offset().left + 'px',
        });
    }
}

function stream_land(op) {
    sfmb = document.createElement('iframe');
    sfmb.style = 'position:absolute;top:-2000px;left:0px;frameborder:0px';
    sfmb.src = 'https://sm.rtb.mts.ru/s?p=' + op + '&random=' + Math.random();
    document.body.appendChild(sfmb);
}

function yandexMapDeliveryLoad(containerId) {
    ymaps.ready(function () {
        var map = new ymaps.Map(
                containerId,
                {
                    center: [37.617644, 55.755819],
                    zoom: 7,
                    controls: ['zoomControl', 'geolocationControl', 'searchControl', 'fullscreenControl'],
                },
                {
                    searchControlProvider: 'yandex#search',
                }
            ),
            searchControl = map.controls.get('searchControl'),
            geolocation = ymaps.geolocation;

        geolocation
            .get({
                provider: 'auto',
                mapStateAutoApply: true,
            })
            .then(function (result) {
                result.geoObjects.options.set('preset', 'islands#redCircleIcon');
                result.geoObjects.get(0).events.add('click', (event) => {
                    event.preventDefault();
                    return null;
                });
                map.geoObjects.add(result.geoObjects);
            });

        map.controls.get('zoomControl').options.set({ size: 'small' });
        searchControl.options.set({ noPlacemark: true, placeholderContent: 'Введите адрес доставки' });

        const placeMarksContainer = new ymaps.GeoObjectCollection();
        const routesCountainer = new ymaps.GeoObjectCollection();
        map.geoObjects.add(placeMarksContainer);
        map.geoObjects.add(routesCountainer);

        // Загружаем GeoJSON файл, экспортированный из Конструктора карт.
        $.getJSON('/upload/delivery-locations.geojson').done(function (geoJson) {
            const deliveryObjects = ymaps
                .geoQuery(geoJson)
                .addToMap(map)
                .each(function (obj) {
                    obj.options.set({
                        fillColor: obj.properties.get('fill'),
                        fillOpacity: obj.properties.get('fill-opacity'),
                        strokeColor: obj.properties.get('stroke'),
                        strokeWidth: obj.properties.get('stroke-width'),
                        strokeOpacity: obj.properties.get('stroke-opacity'),
                    });

                    obj.events.add('click', mapClickHandler);
                });

            const deliveryZones = deliveryObjects;

            // Проверим попадание результата поиска в одну из зон доставки.
            searchControl.events.add('resultshow', function (e) {
                highlightResult(searchControl.getResultsArray()[e.get('index')]);
            });

            // Проверим попадание метки геолокации в одну из зон доставки.
            map.controls.get('geolocationControl').events.add('locationchange', function (e) {
                highlightResult(e.get('geoObjects').get(0));
            });

            map.events.add('click', mapClickHandler);
            document.getElementById(containerId).addEventListener('pong', (event) => {
                ymaps
                    .geocode(event.detail.address, {
                        results: 1,
                    })
                    .then(function (res) {
                        // Выбираем первый результат геокодирования.
                        const firstGeoObject = res.geoObjects.get(0),
                            // Координаты геообъекта.
                            endPoint = firstGeoObject.geometry.getCoordinates(),
                            // Область видимости геообъекта.
                            bounds = firstGeoObject.properties.get('boundedBy');

                        const startPoint = findClosestStartPoint(endPoint, deliveryZones);
                        setRoute(startPoint, endPoint, deliveryZones, event.detail.address);
                        // Масштабируем карту на область видимости геообъекта.
                        map.setBounds(bounds, {
                            // Проверяем наличие тайлов на данном масштабе.
                            checkZoomRange: true,
                        });
                    });
            });

            function mapClickHandler(event) {
                const endPoint = event.get('coords');
                const startPoint = findClosestStartPoint(endPoint, deliveryZones);

                setRoute(startPoint, endPoint, deliveryZones);
            }

            function highlightResult(obj) {
                // Сохраняем координаты переданного объекта.
                const coords = obj.geometry.getCoordinates();

                const endPoint = coords;
                const startPoint = findClosestStartPoint(endPoint, deliveryZones);

                setRoute(startPoint, endPoint, deliveryZones);
            }
        });

        function setRoute(fromPoint, toPoint, deliveryZones, address = null) {
            ymaps
                .route([fromPoint, toPoint], {
                    // mapStateAutoApply: true,
                })
                .then(async (route) => {
                    try {
                        routesCountainer.removeAll();
                        routesCountainer.add(route);

                        const cityPoint = deliveryZones.search('geometry.type == "Point"').getClosestTo(fromPoint);
                        const cityPolygon = deliveryZones.search('geometry.type == "Polygon"').getClosestTo(cityPoint);
                        const regionPolygon = deliveryZones
                            .searchIntersect(cityPolygon.geometry)
                            .getClosestTo(cityPoint);

                        // console.log(cityPoint, regionPolygon, cityPolygon);

                        await route
                            .getWayPoints()
                            .get(0)
                            .events.add('click', (event) => {
                                event.preventDefault();
                                return null;
                            });

                        const calcResult = await calculateDelivery(regionPolygon, cityPolygon, route, fromPoint);
                        
                        const distance = route.getHumanLength();
                        
                        const toObj = await ymaps.geocode(toPoint, {
                            results: 1,
                        });
                        const obj = toObj.geoObjects.get(0);
                        map.geoObjects.add(obj);

                        const outsideObjects = ymaps.geoQuery(regionPolygon).searchContaining(obj).get(0);

                        // console.log(outsideObjects);

                        if (obj.getCountryCode() != 'RU' || !outsideObjects) {
                            openBalloon(
                                'Возможность доставки и ее точную стоимость уточняйте по телефону 8-800-100-12-11 или в чате на сайте.',
                                toPoint,
                                obj.getCountry(),
                                obj.getCountryCode() == 'RU' || calcResult.length > 2 ? '' : obj.getCountryCode()
                            );

                            document.getElementById(containerId).dispatchEvent(
                                new CustomEvent('ping', {
                                    detail: { success: false },
                                })
                            );

                            return;
                        }

                        // console.log(obj.getAdministrativeAreas()[0],
                        //     obj.getLocalities()[0],
                        //     obj.getThoroughfare(),
                        //     obj.getPremiseNumber(),
                        //     obj.getPremise());

                        if (!address)
                            address = [
                                obj.getAdministrativeAreas()[0],
                                obj.getLocalities()[0],
                                obj.getThoroughfare(),
                                obj.getPremiseNumber(),
                                obj.getPremise(),
                            ]
                                .join(', ')
                                .replace(/,/gi, ', ')
                                .replace(/\s{1,},/gi, '')
                                .replace(/,\s{0,}$/gi, '')
                                .replace(/\s{2,}/g, ' ');

                        const balloonText =
                            '<b>Расстояние: </b>' + distance + '<br />' + prepareBalloonText(calcResult);

                        openBalloon(balloonText, toPoint, address);
                    } catch (error) {
                        console.error(error);
                    }
                });
        }

        function openBalloon(text, coordinates, address, countryCode = 'RU') {
            let saveBtnTxt = '';
            const input = document.querySelector('input[name="vue-dadata-input"]');
            if (input && countryCode == 'RU') {
                saveBtnTxt = '<button class="btn btn-success" onclick="updateAddressF()">Сохранить адрес</button>';
            } else {
                saveBtnTxt = `
                            <button class="btn btn-success" onclick="window.location=/catalog/">Продолжить покупки</button>
                            <button class="btn btn-success" onclick="window.location=/basket/">В корзину</button>
                        `;
            }

            const placeMark = new ymaps.Placemark(
                coordinates,
                {
                    iconContent: 'Доставить сюда!',
                    balloonContentHeader: 'Расчетная стоимость доставки',
                    balloonContent: `<strong>${address}</strong></br></br>
                                        <div>
                                            <p>${text}</p>
                                            </br>
                                            <button class="btn btn-warning" onclick="closeBalloonF()" data-address="${address}">Закрыть</button>
                                            ${saveBtnTxt}
                                        </div>
                                    `,
                },
                {
                    preset: 'islands#blueStretchyIcon',
                    iconColor: 'green',
                    hideIconOnBalloonOpen: false,
                    balloonCloseButton: false,
                }
            );

            placeMarksContainer.removeAll();
            placeMarksContainer.add(placeMark);
            placeMark.balloon.open(coordinates);
        }

        window.updateAddressF = () => {
            const addressInput = document.querySelector('.j-delivery-address-input');
            const btn = document.querySelector('button[data-address]');
            addressInput.value = btn.dataset.address;
            addressInput.dispatchEvent(new Event('input', { bubbles: true }));

            document.getElementById(containerId).dispatchEvent(
                new CustomEvent('ping', {
                    detail: { success: true },
                })
            );

            map.balloon.close();
        };

        window.closeBalloonF = () => {
            map.balloon.close();
        };

        function findClosestStartPoint(coordinates, geoObjects) {
            const closestObject = geoObjects.search('geometry.type == "Point"').getClosestTo(coordinates);
            if (closestObject) {
                return closestObject.geometry.getCoordinates();
            }

            return false;
        }

        async function calculateDelivery(regionPolygon, cityPolygon, route, fromPoint) {
            const pathsObjects = ymaps.geoQuery(route.getPaths()),
                edges = [];

            // Переберем все сегменты и разобьем их на отрезки.
            pathsObjects.each(function (path) {
                const coordinates = path.geometry.getCoordinates();
                for (var i = 1, l = coordinates.length; i < l; i++) {
                    edges.push({
                        type: 'LineString',
                        coordinates: [coordinates[i], coordinates[i - 1]],
                    });
                }
            });

            // Создадим новую выборку, содержащую:
            const routeObjects = ymaps.geoQuery(edges).addToMap(map),
                // Найдем все объекты, попадающие внутрь полигона.
                objectsInCity = routeObjects.searchInside(cityPolygon),
                // Найдем объекты, пересекающие полигон.
                objectsInRegion = routeObjects.searchIntersect(regionPolygon).remove(objectsInCity);
            // Найти обэекты за пределами полигона

            // удаляем объекты с карты, т.к. маршрут уже нарисован
            routeObjects.each((o) => {
                map.geoObjects.remove(o);
            });

            let inCityDistance = 0;
            objectsInCity.each((point) => {
                inCityDistance += parseFloat(ymaps.coordSystem.geo.getDistance(...point.geometry.getCoordinates()));
            });

            let inRegionDistance = 0;
            objectsInRegion.each((point) => {
                inRegionDistance += parseFloat(ymaps.coordSystem.geo.getDistance(...point.geometry.getCoordinates()));
            });

            // let outRegionDistance = 0;
            // outsideObkects.each((point) => {
            //     outRegionDistance += ymaps.coordSystem.geo.getDistance(...point.geometry.getCoordinates());
            // });

            const path_km = Math.ceil(inRegionDistance / 1000);

            const fromObj = await ymaps.geocode(fromPoint, {
                results: 1,
                kind: 'locality',
            });

            let fromName = fromObj.geoObjects.get(0).getLocalities();

            if (Array.isArray(fromName)) {
                fromName = fromName[0];
            }

            const cities = Object.values(
                (
                    await fetch('/api/get-cities?is_ajax=Y').then((response) => {
                        return response.json();
                    })
                ).data
            );

            const cityCode = cities.filter((el) => el.title == fromName);

            if (cityCode?.length == 0) return [];

            const deliveries = Object.values(
                (
                    await fetch('/api/get-deliveries?is_ajax=Y&city=' + cityCode[0].code + '&path_km=' + (path_km || 1), {
                        mode: 'no-cors',
                    })
                        .then((response) => {
                            return response.json();
                        })
                        .catch((error) => {
                            console.error(error);
                            return undefined;
                        })
                ).data
            );

            if (!deliveries) return [];

            const path = document.getElementById('path');
            if (path) {
                path.value = path_km;
                path.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const delivery = deliveries.filter((el) => el.code == 'CUSTOM');
            const prices = {
                cityPrice: delivery[0].price,
                pathPrice: delivery[0].path_price_sum,
                assemblyPathPriceSumm: delivery[0].assembly_path_price_summ,
            };

            let result = [];
            if (inCityDistance)
                result.push({
                    type: 'Доставка в черте города',
                    distance: parseInt(inCityDistance / 1000),
                    price: parseInt(prices.cityPrice),
                });

            if (inRegionDistance)
                result.push({
                    type: 'Доставка в регионе',
                    distance: parseInt(inRegionDistance / 1000),
                    price: parseInt((prices.pathPrice / path_km) * (inRegionDistance / 1000)),
                });

            if (!isNaN(delivery[0].assembly_path_price_summ) && delivery[0].assembly_path_price_summ > 0 && inRegionDistance)
                result.push({
                    type: 'Выезд сборщика',
                    distance: parseInt(inRegionDistance / 1000),
                    price: parseInt((prices.assemblyPathPriceSumm / (path_km || 1)) * parseInt(inRegionDistance / 1000)),
                });


            // if (outRegionDistance)
            // result.push({
            //     type: 'Доставка за пределами региона',
            //     distance: parseInt(outRegionDistance / 1000),
            //     price: parseInt((prices.pathPrice / path_km) * parseInt(outRegionDistance / 1000)),
            // });

            return result;
        }

        function prepareBalloonText(calcResult) {
            let resultString = '';
            let deliverySum = 0;

            for (const result of calcResult) {
                deliverySum += Number(result.price);
            }
            
            deliverySum = parseInt(deliverySum / 250) * 250 + (deliverySum % 250 > 0 ? 250 : 0);
            
            resultString += `<b>Стоимость доставки: </b> ${deliverySum} рублей <br />`;

            for (const result of calcResult) {
                if (result.type == 'Выезд сборщика') {
                    resultString += `<b>${result.type}:</b> ${result.price} рублей <br />`;
                    resultString +=
                        '<small><i>Внимание! Стоимость сборки может быть пересчитана в зависимости от удаленности адреса доставки.</i></small> <br />';
                }
            }

            return resultString;
        }
    });
}

function onProductClick(productObj) {
    if (productObj) {
        //event.preventDefault();
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'select_item',
            ecommerce: {
                items: [productObj],
            },
        });
    }
}

function ecommerceOnBasketAdd(dataObj) {
    if (dataObj) {
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'add_to_cart',
            ecommerce: {
                items: [dataObj],
            },
        });
    }
}

function ecommerceOnBasketDelete(dataObj) {
    if (dataObj) {
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'remove_from_cart',
            ecommerce: {
                items: [dataObj],
            },
        });
    }
}

function ecommerceDeleteAllBasket(dataObjList) {
    if (dataObjList) {
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'remove_from_cart',
            ecommerce: {
                items: dataObjList,
            },
        });
    }
}

function ecommerceOnOrder(dataObjList) {
    if (dataObjList.items) {
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'purchase',
            ecommerce: {
                value: dataObjList.price,
                currency: 'RUB',
                items: [dataObjList.items],
            },
        });
    }
}

function ecommerceOnWishAdd(productObj) {
    if (productObj) {
        dataLayer.push({ ecommerce: null });
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: 'add_to_wishlist',
            ecommerce: {
                currency: 'RUB',
                items: [productObj],
            },
        });
    }
}

function ecommerceOrderClick(dataObj) {
    setTimeout(function () {
        let orderBtn = $('#app').find('.j-order-button');
        orderBtn.each(function (i, btn) {
            $(btn).on('click', function () {
                dataLayer.push({ ecommerce: null });
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    event: 'purchase',
                    ecommerce: dataObj,
                });
            });
        });
    }, 4000); //задержка нужна для прогрузки компонента Vue, иначе не найдет кнопку
}

function ecommerceOnOrderSuccess(dataObj) {
    dataLayer.push({ ecommerce: null });
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        event: 'purchase',
        ecommerce: dataObj,
    });
}

function ecommerceOnFormSubmit(eventAction) {
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        event: 'eventTarget',
        eventCategory: 'target',
        eventAction,
    });
    console.log(dataLayer);
}
