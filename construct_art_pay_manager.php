<?php
/**
 * @author Artsiom Kirkor <info@kas.by>
 * @copyright Copyright (c) 2015, Artsiom Kirkor
 * @version 1.0
 *
 *
 * Данные по карте:
 *
 * ---- NUM: 4344 8601 5033 7685
 * ---- CVC: 271
 * ---- EXP: 07/15
 *
 * Лимиты:
 *
 * Количество возвратов в день....................................................2;
 * Сумма возвратов в день......................................................2000;
 * Максимальное количество отклоненных транзакций в день.........................15;
 * Максимальное количество любых транзакций с использованием 1 карты/день........10;
 * Максимальная сумма любых транзакций с использованием 1 карты/день..........10000;
 * Минимальная сумма транзакции................................................1000;
 * Максимальная сумма транзакции...............................................3000;
 * Максимальное число транзакций/день............................................20;
 * Максимальная сумма транзакций/день.........................................20000;
*/
class construct_art_pay_manager
{
    /**
     * Лимиты
     * TR (transaction)
    */
    const TR_MAX_LIM            = 3000;
    const TR_MIN_LIM            = 1000;
    /**
     * Понижающий коэфициент.
    */
    const TR_COEF               = 0.1;

    /**
     * Уникальный идентификатор интернет-магазина
     * в системе Artpay
    */
    const AP_ST_ID              = 'ap_storeid';

    /**
     * Название магазина, которое будет  отображаться
     * на форме оплаты.
    */
    const AP_ST                 = 'ap_store';

    /**
     * Номер заказа (может быть только числом)
    */
    const INT_AP_ORD_NUM        = 'ap_order_num';

    /**
     * Числовой трехзначный код валюты согласно ISO4271
    */
    const AP_CUR_ID             = 'ap_currency_id';

    /**
     * Версия системы
    */
    const AP_V                  = 'ap_version';

    /**
     * Случайная последовательность символов участвующих в
     * формировании электронной подписи заказа.( использовать
     * значение из примера реализации отправки запроса от
     * интернет-магазина на тестовую платформу).
    */
    const AP_SEED               = 'ap_seed';

    /**
     * Контрольное значение (электронная подпись) заказа.
     * Данное значение является hex-последовательностью.
     * ( использовать значение из примера реализации отправки
     * запроса от интернет-магазина на тестовыю платформу).
     */
    const AP_SIGN               = 'ap_signature';

    /**
     * Url магазина для возврата в случае успешой оплаты(если
     * на сайте используются ссылки с параметрами
     * http://shop.by/index.php?param=1&page=success, их нужно
     * предварительно преобразовывать через htmlspecialchars перед
     * отправкой в artPay)
    */
    const AP_RET_URL            = 'ap_return_url';

    /**
     * Url магазина для возврата в случае ошибки оплаты(Если
     * на сайте используются ссылки с параметрами
     * http://shop.by/index.php?param=1&page=success, их нужно
     * предварительно преобразовывать через htmlspecialchars перед
     * отправкой в artPay)
    */
    const AP_C_RET_URL          = 'ap_cancel_return_url';

    /**
     * Url магазина для  возврата  в случае успешной оплаты
     * ()ap_order_num (номер заказа внутри магазина) , status=OK
     * (Если на сайте используются ссылки с параметрами
     * http://shop.by/index.php?param=1&page=success, их нужно
     * предварительно преобразовывать через htmlspecialchars перед
     * отправкой в artPay)
    */
    const AP_SYS_URL            = 'ap_system_url';

    /**
     * Тестовый запрос[1/0]
    */
    const AP_TEST               = 'ap_test';

    /**
     * Названия наименований товаров, разделитель
     * |(названия товаров не принимаются в кириллице, только символы
     * латинского алфавита, иначе нужно проводить транслитерацию;)
    */
    const AP_N                  = 'ap_invoice_item_name';

    /**
     * Количества наименований товаров, разделитель |
    */
    const AP_C                  = 'ap_invoice_item_quantiny';

    /**
     * Цены наименований товаров, разделитель |
    */
    const AP_P                  = 'ap_invoice_item_price';

    /**
     * Общая сумма заказа (при работе с тестовой платформой
     * не должна превышать лимитов)
    */
    const AP_T                  = 'ap_total';

    /**
     * Логин интернет-магазина
    */
    const AP_LOGIN              = 'login';

    /**
     * Пароль Интернет магазина
    */
    const AP_PSW                = 'pswd';

    /**
     * Рабочий язык
    */
    const AP_LANG               = 'lang';

    /**
     * Данные клиентской стороны (маркировака CL)
     * Ключи ассоциативного массива значения которых
     * представляют собой массив передаваемых параметров.
    */
    const CL_AP_N               = 'ap_n';
    const CL_AP_C               = 'ap_c';
    const CL_AP_P               = 'ap_p';
    const CL_AP_T               = 'ap_t';

    /**
     * КОНСТАНТЫ ОТВЕТА ВОЗВРАЩАЕМЫЕ СЕРВЕРОМ ART_PAY,
     * ПОСЛЕ ПОДАЧИ CURL-ЗАПРОСА С ДАННЫМИ СТОИМОСТИ ТОВАРА.
    */
    const RESP_ERR_CODE         = 'errorCode';
    const RESP_ERR_DESC         = 'errorDesc';
    const RESP_URL              = 'paymentFormUrl';

    /**
     * Разделитель директории
    */
    const DS                    = DIRECTORY_SEPARATOR;

    /**
     * Домен подключаемого проекта.
    */
    protected $ap_domain        = 'https://zorachka.by';

    /**
     * Параметр ответа (добавляется после домена).
    */
    protected $prm              = '?q=';

    /**
     * Номер текущей заявки.
    */
    protected $order            = '';

    /**
     * Свойство включает|отключает тестовый режим
     * работы приложения.
    */
    protected $isTestMode       = true;

    /**
     * Разделитель параметров
    */
    protected $delim            = '|';

    /**
     * Свойство содержит сертификат, который должен использовать проект при соединении с
     * системой Artpay в опции CURLOPT_CAINFO мы указываем путь к нему
     * (высылается вместе с другими параметрами для магазина.
    */
    protected $cert             = 'ssl/cacert.pem';

    /**
     * Массив содержащий данные оплаты с клиентской стороны.
    */
    protected $data             = [];

    /**
     * Обязательные ключи элементов массива $this->data.
     * В качестве значений элементы содержат ключи массива CURL.
     * Все элементы кроме CL_AP_N, должны быть обработаны как
     * integer-тип данных.
    */
    protected $client_keys = array
    (
        self::CL_AP_N => self::AP_N,
        self::CL_AP_C => self::AP_C,
        self::CL_AP_P => self::AP_P,
        self::CL_AP_T => self::AP_T
    );

    /**
     * Массив содержит данные клиентского заказа,
     * которые будут отправлены на сервер ArtPay
     * посредством Curl отправки.
    */
    protected $curl_arr         = [];

    /**
     * Параметры тестовой среды.
     * ArtPay testing params
    */
    protected $ap_tp            = array
    (
        self::AP_ST_ID      => 113001,
        self::AP_ST         => 'shop_test_bib',
        self::AP_LOGIN      => 'shop_test_bib',
        self::AP_PSW        => 'shop_test_42',
        self::AP_LANG       => 'en',
        self::AP_SEED       => 00000000,
        self::AP_SIGN       => 'sdsdsad8d4395dab7598c5f4b94d5bc4780f4af',
    );

    /**
     * Параметры рабочей среды
     * ArtPay work params
    */
    protected $ap_wp            = array
    (
        self::AP_ST_ID      => 235001,
        self::AP_ST         => 'EXAMPLE',
        self::AP_LOGIN      => 'EXAMPLE_shop',
        self::AP_PSW        => 'EXAMPLE',
        self::AP_LANG       => 'en',
        self::AP_SEED       => 00000000,

        //Электронная подпись (устанавливается динамически).
        self::AP_SIGN       => '',
    );

    /**
     * AP_URL
    */
    protected $ap_url           = array
    (
        'state=1&&order=', // URL успешной оплаты.
        'state=0&&order=', // URL ошибки.
        'state=0&&order=', // URL ошибки.
    );

    /**
     * URL для формирования запроса.
    */
    protected $ap_t_url = 'https://engine.3c.by/service/order/create';
    protected $ap_w_url = 'https://engine.artpay.by/service/order/create';

    /**
     * Стандартный набор параметров (используется для рабочей
     * и тестовой среды)
     * Artpay default params
    */
    protected $ap_dp            = array();

    /**
     * Ссылка для перехода на форму оплаты системы ArtPay™ ,
     * для ввода реквизитов пластиковой карточки
     * (переход осуществляется POST запросом).
    */
    protected $response_url     = '';


    protected function __construct()
    {
        /**
         * Данные клиентской стороны.
        */
        $this->data  = $_POST;

        /**
         * Установить сертификат.
        */
        $this->setCertDir();

        /**
         * Установить номер текущей заявки.
        */
        $this->order = $this->getOrderNum();
    }

    /**
     * Метод возвращает номер текущей заявки.
    */
    protected function getOrderNum()
    {
        return rand(999,9999);
    }

    /**
     * Метод определяет в каком режиме работает
     * приложение.
     *
     * @param bool $asInt
     * Если аргументу передать значение true, метод
     * вернет integer ( 0 | 1 ).
     *
     * @return bool|integer
    */
    protected function test($asInt = false)
    {
        return $asInt ?
            (int)($this->data[self::AP_TEST]) :
            (bool)($this->data[self::AP_TEST]);
    }

    /**
     * Метод позволяет получить значение цены товара игнорируя пробелы и
     * прочие символы, которые не относятся к цене.
     *
     * @param mixed $price
     * @return bool
     *
    */
    protected function getPriceVal($price = false)
    {
        if
        (
            !$price             ||
            !is_scalar($price)
        )
        {
            return 0;
        }

        $prc = (int)(@preg_replace('/[^\d]+/', '', $price));
        return $prc ?: 0;
    }

    /**
     * Метод подсчитывает суммарную стоимость товаров.
     *
     * @param array $prc
     * Ассоциативный массив с ценами элементов.
     * Ключ - self::CL_AP_P.
     *
     * @param array $cnt
     * Ассоциативный массив с заданным количеством элементов.
     * Ключ - self::CL_AP_С.
     *
     * @return integer
     */
    protected function calculateTotalPrice($prc = [], $cnt = [])
    {
        if
        (
            !$this->arr($prc)   ||
            !$this->arr($cnt)
        )
        {
            return false;
        }

        $prc_t = 0;

        /**
         * $i = 1;
         * Индексация массива начинается с 1. (JS)
        */
        for($i = 1; $i <= count($prc); $i++)
        {
            /**
             * Проверяем количество по каждому товару.
            */
            if
            (
                !$prc[$i]   ||
                !$cnt[$i]
            )
            {
                return false;
            }

            $prc_t += $prc[$i] * $cnt[$i];
        }


        return $prc_t ?: 0;
    }

    /**
     *
     * @param array $price
     *
     * Метод устанавливает лимит транзакций в соответствии
     * с данными "лимитов".
     *
     * Значение передаваемой цены в тестовом режиме игнорируется,
     * в учет берется количество товаров.
     *
     * @return bool|array
     */
    protected function setTransactionLimit($price = [])
    {
        if (!$this->arr($price)) {
            return false;
        }

        /**
         * Общее количество товаров.
        */
        $cnt = count($price);

        /**
         * Устанавливаем среднюю стоимость товара используя максимальный
         * лимит одной транзакции.
        */
        $prc = @round
        (
            (self::TR_MAX_LIM/$cnt) * self::TR_COEF,
            0
        );

        foreach($price as $k => $v){
            $price[$k] = $prc;
        }

        return $price;
    }

    /**
     * @param array $price
     * @return array
    */
    protected function checkTotalPrice($price = [])
    {
        if (!$this->arr($price)) {
            return [];
        }

        /**
         * Получаем общую стоимость товаров
        */
        $price = [@array_sum($price)];

        /**
         * Возвращаем общую стоимость как массив.
        */
        return $price ?: [];
    }

    /**
     * @param array $data
     * @return bool
     *
     * Метод проверяет тип передаваемого аргумента.
     * Если аргумент является массивом, метод возвращает true;
    */
    protected function arr($data = [])
    {
        if
        (
            empty($data)        ||
            !is_array($data)
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Метод устанавливает путь к сертификату, который должен использовать проект при соединении с
     * системой Artpay в опции CURLOPT_CAINFO мы указываем путь к нему (высылается вместе с другими
     * параметрами для магазина.
    */
    protected function setCertDir()
    {
        $path  = __DIR__ . self::DS . $this->cert;

        /**
         * Устанавливаем слэши в одном направлении.
        */
        $path  = str_replace('\\', '/', $path);

        file_exists($path) ?
            $this->cert = $path :
            $this->cert = '';

        return (bool)($this->cert);
    }

    /**
     * Метод осуществляет отправку запросов на сервер.
     *
     * @param array $req
     * @return mixed
    */
    protected function cUrl($req = [])
    {
        if (!$this->arr($req)) {
            return false;
        }

        /**
         * Создаем новый ресурс cURL
        */
        $ch = curl_init();

        /**
         * Устанавливаем несколько параметров для сеанса cURL
         * @return bool
        */
        if (!curl_setopt_array($ch, $req)) {
            return false;
        }

        /**
         * Получение данных ответа от сервера.
        */
        $resp = curl_exec($ch);

        /**
         * Завершение сеанса и освобождение ресурсов.
        */
        curl_close($ch);

        /**
         * Возвращаем данные ответа.
        */
        return $resp ?
            $resp : false;
    }

    /**
     * Метод устанавливает и отправляет данные на сервера ArtPay
    */
    protected function req()
    {
        if (!$this->cert) return false;

        //var_dump($this->curl_arr);
        //return;

        /**
         * Устанавливаем параметры cURL-запроса
         * для отправки на сервер.
        */
        $req = array
        (
            CURLOPT_URL                 => $this->getReqUrl(),
            CURLOPT_VERBOSE             => 1,
            CURLOPT_HEADER              => 0,
            CURLOPT_POST                => 1,
            CURLOPT_POSTFIELDS          => http_build_query($this->curl_arr),
            CURLOPT_SSL_VERIFYHOST      => 0,
            CURLOPT_SSL_VERIFYPEER      => false,
            CURLOPT_CAINFO              => $this->cert,
            CURLOPT_RETURNTRANSFER      => 1,
            CURLOPT_COOKIEJAR           => '',
            CURLOPT_COOKIEFILE          => '',
            CURLOPT_USERAGENT           => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_REFERER             => $this->ap_url[0]
        );

        $resp = $this->cUrl($req);

        if
        (
            !$resp                          ||
            !$this->getResponseData($resp)  ||
            !$this->response_url
        )
        {
            return false;
        }

        /**
         * Формируем запрос для заполнения данных карт-счета,
         * для этого используем существующий массив $req c
         * некоторыми изменениями.
        */
        $req[CURLOPT_URL] = $this->response_url;

        /**
         * Возвращаем полученную ссылку для оплаты.
        */
        return $this->response_url;
    }

    /**
     * Метод определяет полученный ответ от сервера ArtPay, который
     * был передан методом $this->req() и возвращает true в случае
     * удачного формирования запроса.
     *
     * В свойство $this->response_url
     * записывается ссылка перехода для заполнения данных карт-счета.
     *
     * В случае ошибки метод возвращает false.
     *
     * @param string $resp
     * @return bool
    */
    protected function getResponseData($resp = '')
    {
        if (!$resp) return false;

        /**
         * Возвратить значение как ассоциативный массив.
        */
        $resp = json_decode($resp, true);

        /**
         * Определяем ошибку.
        */
        if (!$this->arr($resp))
        {
            switch (json_last_error())
            {
                //' - Ошибок нет';
                case JSON_ERROR_NONE:
                break;

                //' - Достигнута максимальная глубина стека';
                case JSON_ERROR_DEPTH:
                break;

                //' - Некорректные разряды или не совпадение режимов';
                case JSON_ERROR_STATE_MISMATCH:
                break;

                //' - Некорректный управляющий символ';
                case JSON_ERROR_CTRL_CHAR:
                break;

                //' - Синтаксическая ошибка, не корректный JSON';
                case JSON_ERROR_SYNTAX:
                break;

                //' - Некорректные символы UTF-8, возможно неверная кодировка';
                case JSON_ERROR_UTF8:
                break;

                //' - Неизвестная ошибка';
                default:
                break;
            }

            return false;
        }

        /**
         * Сервер ArtPay возвратил ошибку.
        */
        if (!empty($resp[self::RESP_ERR_CODE])) {
            return false;
        }

        /**
         * Сохраняем данные ответа.
        */
        $this->response_url = $resp[self::RESP_URL];
        return true;
    }

    /**
     * Метод возвращает URL-адрес ответа на установленый
     * домен $this->ap_domain
     *
     * @param string $order - номер заявки.
     * @return bool
    */
    protected function setReturnUrls($order = '')
    {
        if (!$order) return false;

        $urls = array
        (
            self::AP_RET_URL    => $this->ap_url[0],
            self::AP_C_RET_URL  => $this->ap_url[1],
            self::AP_SYS_URL    => $this->ap_url[2]
        );

        foreach($urls as $k => $v){
            $this->curl_arr[$k] = $this->ap_domain . $this->prm . base64_encode($v . $order);
        }

        return true;
    }

    /**
     * Данный метод устанавливает параметры
     * тестовой/рабочей среды.
    */
    protected function setCurlData()
    {
        switch($this->test())
        {
            case true:

                if (!is_array($this->ap_tp)) {
                    return false;
                }

                $params = $this->ap_tp;

            break;

            case false:

                if (!is_array($this->ap_wp)) {
                    return false;
                }

                /**
                 * Установить электронную подпись заказа (хэш-последовательность).
                */
                $this->ap_wp[self::AP_SIGN] = md5(mktime());

                /**
                 * Запись параметров рабочей среды.
                */
                $params = $this->ap_wp;

            break;

            default:
                return false;
            break;
        }

        foreach($params as $k => $v)
        {
            $this->curl_arr[$k] = $v;
        }

        if (!is_array($this->curl_arr)) {
            return false;
        }

        //Установить номер заявки.
        $this->curl_arr[self::INT_AP_ORD_NUM] = $this->order;

        //Установить URL-адреса ответа.
        if (!$this->setReturnUrls($this->order)) {
            return false;
        }

        return true;
    }

    /**
     * Взависимости от режима работы приложения данный метод
     * возвращает URL для формирования запроса на оплату заказа.
    */
    protected function getReqUrl()
    {
        return $this->test() ?
            $this->ap_t_url :
            $this->ap_w_url;
    }

    /**
     * Метод формирует персональные данные проекта для
     * тестового/рабочего режима.
    */
    protected function setCurlProjData()
    {
        switch($this->test())
        {
            case true:

                //Установить параметры тестовой среды.
                if (!$this->setCurlData()) return false;

                //Числовой трехзначный код валюты согласно ISO4271
                $this->curl_arr[self::AP_CUR_ID]      = 974;

                //Версия системы ArtPay
                $this->curl_arr[self::AP_V]           = 2;

                //Тестовый запрос [0/1]
                $this->curl_arr[self::AP_TEST]        = $this->test(true);

            break;

            case false:

                //Установить параметры рабочей среды.
                if (!$this->setCurlData()) return false;

                //Числовой трехзначный код валюты согласно ISO4271
                $this->curl_arr[self::AP_CUR_ID]      = 974;

                //Версия системы ArtPay
                $this->curl_arr[self::AP_V]           = 2;

                //Тестовый запрос [0/1]
                $this->curl_arr[self::AP_TEST]        = $this->test(true);

            break;
        }

        return true;
    }

    /**
     * Метод устанавливает данные клиентского заказа в
     * соответствии со всеми параметрами валидации текущего
     * значения.
     *
     * @param string $k
     * @param array $data
     * @return bool
    */
    protected function setCurlArr($k = '', $data = [])
    {
        if
        (
            !$k                         ||
            !$this->arr($data)          ||
            !$this->client_keys[$k]
        )
        {
            return false;
        }

        /**
         * Устанавливать параметры в соответствии
         * с заданным режимом.
        */
        switch($this->test(true))
        {
            /**
             * Рабочий режим отправки
            */
            case 0:
                //void
            break;

            /**
             * Тестовый режим отправки (с учетом лимитов)
             *
             * Характеристики:
             *
             * Количество наименований товаров.................1;
             * Стоимость одного товара/услуги.........TR_MAX_LIM; (3000)
             * Общая стоимость товаров/услуг..........TR_MAX_LIM; (3000)
            */
            case 1:

                /**
                 * Наименование
                */
                $k !== self::CL_AP_N ?:
                    $data = array($this->data[self::CL_AP_N][1]);
                /**
                 * Количество
                */
                $k !== self::CL_AP_C ?:
                    $data = array(1);

                /**
                 * Цена 1 товара/услуги.
                */
                $k !== self::CL_AP_P ?:
                    $data = array(self::TR_MAX_LIM);

                /**
                 * Общая цена.
                */
                $k !== self::CL_AP_T ?:
                    $data = array(self::TR_MAX_LIM);

            break;
        }


        /**
         * Преобразовать элементы в строку добавляя
         * разделитель между ними.
        */
        $data = @implode($this->delim, $data);

        /**
         * Добавляем разделитель в конец строки.
         * (согласно требований технической документации).
        */
        $k !== self::CL_AP_T ?
            $data .= $this->delim : false;

        /**
         * Установить параметры.
        */
        $this->curl_arr[$this->client_keys[$k]] = $data;
        return true;
    }

    /**
     * Метод проверяет и устанавливает данные пользователя
     * для последующей отправки на сервер
    */
    protected function clientValidate()
    {

        if (!$this->arr($this->data)) {
            return false;
        }

        /**
         * Наименования товаров и цены должны
         * совпадать по числу элементов в массиве.
        */
        if(count($this->data[self::CL_AP_N]) !== count($this->data[self::CL_AP_P]))
        {
            return false;
        }


        foreach($this->client_keys as $key => $curl_key)
        {
            /**
             * Каждый элемент должен быть массивом.
            */
            if (!$this->arr($this->data[$key]))
            {
                return false;
                break;
            }

            /**
             * Обрабатываются данные типа integer (все кроме названий товаров)
            */
            if ($key !== self::CL_AP_N)
            {
                $ans = array_walk($this->data[$key], function(&$v)
                {
                    /**
                     * Гибкая обработка цен.
                    */
                    $v = $this->getPriceVal($v);
                    return $v ? true : false;
                });

                /**
                 * Данные не должны быть нулевыми
                 */
                if (!$ans) {
                    return false;
                }
            }

            switch($key)
            {
                case self::CL_AP_P:

                    /**
                     * Подсчитать сумарную стоимость товаров.
                    */
                    $prc_t = $this->calculateTotalPrice
                    (
                        $this->data[self::CL_AP_P],
                        $this->data[self::CL_AP_C]
                    );

                    if (!$prc_t)
                    {
                        return false;
                        break;
                    }

                    /**
                     * Установить общую стоимость товаров (услуг)
                    */
                    $this->data[self::CL_AP_T] = [$prc_t];

                break;

                default:
                break;
            }

            /**
             * Установить данные клиентского заказа.
            */
            $this->setCurlArr
            (
                $key,
                $this->data[$key]
            );

        }

        return true;
    }

    protected function config()
    {
        if
        (
            !$this->setCurlProjData()   ||
            !$this->clientValidate()    ||
            !$this->req()
        )
        {
            return 0;
        }

        return $this->response_url ?: 0;
    }

    static public function run()
    {
        $obj = new static();
        return $obj->config();
    }
} 