<?php
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\BusinessValue;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Diag\Debug;

function sendOrderToPayKeeper($order_id, $status)
{
    if ($status === 'N' || empty($order_id)) {
        return;
    }

    $order = \Bitrix\Sale\Order::load($order_id);

    $pid = data_get($order->getPaymentSystemId(), "0", null);

    if (!$order || empty($pid) || $pid == 12) {
        return;
    }

    $basket = $order->getBasket();

    $basketItems = $basket->getBasketItems();

    $collection = $order->getPropertyCollection();

    $email = $collection->getUserEmail()->getValue();

    $delivery = $order->getDeliveryPrice();

    $cart = [];

    foreach ($basketItems as $basketItem) {
        /**@var $basketItem Bitrix\Sale\BasketItem */

        $cart[] = [
            'name' => truncateString($basketItem->getField('NAME')),
            'payment_type' => 'prepay',
            'price' => $basketItem->getField('PRICE'),
            'quantity' => $basketItem->getField('QUANTITY'),
            'sum' => $basketItem->getField('PRICE') * $basketItem->getField('QUANTITY'),
            'tax' => 'vat20',
        ];
    }

    if(!empty($delivery)){
        $cart[] = [
            'name' => "Доставка",
            'price' => $delivery,
            'quantity' => 1,
            'sum' => $delivery,
            'tax' => 'vat20',
            'item_type' => 'service',
            'payment_type' => 'prepay',
        ];
    }

    $receiptData = [
        'payment_id' => '',
        'type' => 'sale',
        'is_post_sale' => 'false',
        'is_correction' => 'false',
        'refund_id'           => '',
        'contact' => $email,
        'sum_cashless' => $order->getSumPaid(),
        'sum_cash'            => 0,
        'sum_advance'         => 0,
        'sum_credit'          => 0,
        'sum_counter_payment' => 0,
        'cart' => json_encode($cart),
        'receipt_key' => sha1($_SERVER['HTTP_HOST'] . $order_id),
    ];

    // Получаем PayKeeper для печати чеков
    $paykeeperPS = Manager::getObjectById(12);

    $formUrl = BusinessValue::get('PAYKEEPER_FORM_URL', $paykeeperPS->getConsumerName());
    $lkLogin = BusinessValue::get('PAYKEEPER_LK_LOGIN', $paykeeperPS->getConsumerName());
    $lkPassword = BusinessValue::get('PAYKEEPER_LK_PASSWORD', $paykeeperPS->getConsumerName());

    $parsedUrl = parse_url($formUrl);
    $basicAuthString = base64_encode( $lkLogin . ':' . $lkPassword );

    $domain = $parsedUrl['host'];

    $tokenHeaders = [];
    $tokenHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
    $tokenHeaders['Authorization'] = 'Basic ' . $basicAuthString;

    $httpClient = new HttpClient();
    $httpClient->setHeaders($tokenHeaders);
    $response = $httpClient->get("https://" . $domain . "/info/settings/token");
    $response = json_decode($response, true);

    if(empty($response['token'])){
        Debug::writeToFile("Ошибка авторизации на сервисе PayKeeper для заказа №" . $order_id);
        return;
    }

    $token = $response['token'];
    $request = http_build_query(array_merge($receiptData, array('token' => $token)));

    $httpClient = new HttpClient();
    $httpClient->setHeaders($tokenHeaders);
    $response = $httpClient->post(
        "https://" . $domain . "/change/receipt/print/" ,
        $request
    );
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "https://" . $domain . "/change/receipt/print/");
    curl_setopt($curl, CURLOPT_HTTPHEADER , $tokenHeaders);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($response, true);
}


if (!function_exists('normalizePhoneNumber')) {
    /**
     * Normalize Russian phone numbers to international format.
     *
     * @param string $phoneNumber The phone number to normalize.
     * @return string The normalized phone number in international format.
     */
    function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all characters except digits and the plus sign
        $sanitizedNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        // Strip leading plus sign for easier manipulation
        $strippedNumber = ltrim($sanitizedNumber, '+');

        // Handle different cases based on the length and starting digit
        switch (true) {
            case strlen($strippedNumber) === 11 && strpos($strippedNumber, '7') === 0:
                // Number starts with '7' and has 11 digits
                {
                    $number = '+' . $strippedNumber;
                }
                break;

            case strlen($strippedNumber) === 11 && strpos($strippedNumber, '8') === 0:
                // Number starts with '8' and has 11 digits
                {
                    $number = '+7' . substr($strippedNumber, 1);
                }
                break;

            case strlen($strippedNumber) === 10:
                {
                    // Number has 10 digits
                    $number = '+7' . $strippedNumber;
                }
                break;

            default:
                {
                    // Return the sanitized number if it doesn't match any of the above cases
                    $number = $sanitizedNumber;
                }
                break;
        }

        return  $number;
    }
}

if (!function_exists('truncateString')) {
    /**
     * Truncate a string to a maximum length of 128 characters, including ellipsis.
     *
     * @param string $input The input string to be truncated.
     * @param int $maxLength The maximum length of the truncated string, including ellipsis.
     * @return string The truncated string with ellipsis.
     */
    function truncateString(string $input, int $maxLength = 128): string
    {
        // Define the ellipsis
        $ellipsis = '...';

        // Calculate the maximum length of the truncated string excluding the ellipsis
        $truncateLength = $maxLength - mb_strlen($ellipsis);

        // Check if truncation is necessary
        if (mb_strlen($input) <= $maxLength) {
            return $input;
        }

        // Truncate the input string and append the ellipsis
        return mb_substr($input, 0, $truncateLength) . $ellipsis;
    }
}
