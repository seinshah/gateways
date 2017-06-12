<?php

namespace Hossein\Gateway;

class Language
{
    /**
     * @var string Language of returned message
     */
    protected static $lang = 'fa';

    /**
     * @param $index mixed the index or array of indexes that you want to get their message
     * @return mixed
     * @throws GatewayException
     */
    public static function get($index)
    {
        $index = strtolower($index);
        $method = 'lang_' . self::$lang;

        if(method_exists(get_called_class(), $method))
        {
            $translations = self::$method();

            if(is_array($index))
            {
                $result = [];

                foreach ($index as $key)
                {
                    $result[$key] = (isset($translations[$key])) ? $translations[$key] : $translations['not_exist_index'];
                }

                return $result;
            }
            else
            {
                return (isset($translations[$index])) ? $translations[$index] : $translations['not_exist_index'];
            }
        }
        else
            return 'Language is not supported yet.';
    }

    /**
     * @return array contains key values of error message in persian (farsi)
     */
    protected static function lang_fa()
    {
        return [
            'not_exist_index'               => 'پیام مناسبی برای این مورد در دسترس نیست',
            'nusoap_absence'                => 'برای استفاده از این پکیج ابتدا باید Nusoap را اضافه نمایید.',
            'api_absence'                   => 'مقدار API Key یا Terminal ID تعیین نشده است',
            'username_absence'              => 'نام کاربری تعیین نشده است',
            'password_absence'              => 'رمزعبور تعیین نشده است',
            'amount_absence'                => 'مبلغ فاکتور تعیین نشده است',
            'callback_absence'              => 'لینک بازگشت از بانک تعیین نشده است',
            'webservice_invalid'            => 'آدرس سرور عملیاتی وب سرویس را بررسی کنید. ارتباط با وب سرویس بانک برقرار نشد.',
            'amount_invalid'                => 'مبلغ فاکتور باید از نوع Int و مقدار آن بزرگتر از 0 باشد',
            'callback_invalid'              => 'نوع لینک بازگشت از بانک باید یک URL معتبر باشد',
            'order_id_absence'              => 'مقدار order_id تعیین نشده است',
            'order_id_invalid'              => 'مقدار order_id به صورت عددی وارد نشده است',
            'start_payment_invalid'         => 'متاسفانه قادر به اتصال به بانک جهت شروع تراکنش نیستیم.',
            'start_respond_invalid'         => 'پاسخ بانک برای شروع تراکنش نامعتبر است.',
            'verify_payment_invalid'        => 'متاسفانه قادر به اتصال به بانک جهت پیگیری تراکنش نیستیم.',
            'verify_respond_invalid'        => 'پاسخ بانک برای پیگیری تراکنش نامعتبر است.',
            'mellat_code_0'                 => 'تراکنش با موفقیت انجام شد.',
            'mellat_code_11'                => 'شماره کارت نامعتبر است.',
            'mellat_code_12'                => 'موجودی حساب شما کافی نیست.',
            'mellat_code_13'                => 'رمز نادرست است.',
            'mellat_code_14'                => 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است.',
            'mellat_code_15'                => 'کارت نامعتبر است.',
            'mellat_code_16'                => 'ﺩﻓﻌﺎﺕ ﺑﺮﺩﺍﺷﺖ ﻭﺟﻪ ﺑﻴﺶ ﺍﺯ ﺣﺪ ﻣﺠﺎﺯ ﺍﺳﺖ',
            'mellat_code_17'                => 'ﻛﺎﺭﺑﺮ ﺍﺯ ﺍﻧﺠﺎﻡ ﺗﺮﺍﻛﻨﺶ ﻣﻨﺼﺮﻑ ﺷﺪﻩ ﺍﺳت',
            'mellat_code_18'                => 'ﺗﺎﺭﻳﺦ ﺍﻧﻘﻀﺎﻱ ﻛﺎﺭﺕ ﮔﺬﺷﺘﻪ ﺍﺳت',
            'mellat_code_19'                => 'ﻣﺒﻠﻎ ﺑﺮﺩﺍﺷﺖ ﻭﺟﻪ ﺑﻴﺶ ﺍﺯ ﺣﺪ ﻣﺠﺎﺯ ﺍﺳت',
            'mellat_code_21'                => 'ﭘﺬﻳﺮﻧﺪﻩ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_23'                => 'ﺧﻄﺎﻱ ﺍﻣﻨﻴﺘﻲ ﺭﺥ ﺩﺍﺩﻩ ﺍﺳت',
            'mellat_code_24'                => 'ﺍﻃﻼﻋﺎﺕ ﻛﺎﺭﺑﺮﻱ ﭘﺬﻳﺮﻧﺪﻩ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_25'                => 'ﻣﺒﻠﻎ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_31'                => 'ﭘﺎﺳﺦ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_32'                => 'ﻓﺮﻣﺖ ﺍﻃﻼﻋﺎﺕ ﻭﺍﺭﺩ ﺷﺪﻩ ﺻﺤﻴﺢ ﻧﻤﻲ ﺑﺎﺷﺪ',
            'mellat_code_33'                => 'ﺣﺴﺎﺏ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_34'                => 'ﺧﻄﺎﻱ ﺳﻴﺴﺘﻤی',
            'mellat_code_35'                => 'تاریخ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_41'                => 'ﺷﻤﺎﺭﻩ ﺩﺭﺧﻮﺍﺳﺖ ﺗﻜﺮﺍﺭﻱ ﺍﺳت',
            'mellat_code_42'                => 'تراکنش Sale یافت نشد',
            'mellat_code_43'                => 'قبلا درخواست Verfiy داده شده ﺍﺳت',
            'mellat_code_44'                => 'درخواست Verify یافت نشد',
            'mellat_code_45'                => 'تراکنش Settle شده است',
            'mellat_code_46'                => 'تراکنش Settle نشده است',
            'mellat_code_47'                => 'تراکنش Settle یافت نشد',
            'mellat_code_48'                => 'تراکنش Reverse شده است',
            'mellat_code_49'                => 'تراکنش Refund یافت نشد',
            'mellat_code_51'                => 'ﺗﺮﺍﻛﻨﺶ ﺗﻜﺮﺍﺭﻱ ﺍﺳت',
            'mellat_code_54'                => 'ﺗﺮﺍﻛﻨﺶ ﻣﺮﺟﻊ ﻣﻮﺟﻮﺩ ﻧﻴست',
            'mellat_code_55'                => 'ﺗﺮﺍﻛﻨﺶ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_61'                => 'ﺧﻄﺎ ﺩﺭ ﻭﺍﺭﻳز',
            'mellat_code_111'               => 'ﺻﺎﺩﺭ ﻛﻨﻨﺪﻩ ﻛﺎﺭﺕ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_112'               => 'ﺧﻄﺎﻱ ﺳﻮﻳﻴﭻ ﺻﺎﺩﺭ ﻛﻨﻨﺪﻩ ﻛﺎﺭت',
            'mellat_code_113'               => 'ﭘﺎﺳﺨﻲ ﺍﺯ ﺻﺎﺩﺭ ﻛﻨﻨﺪﻩ ﻛﺎﺭﺕ ﺩﺭﻳﺎﻓﺖ ﻧﺸد',
            'mellat_code_114'               => 'ﺩﺍﺭﻧﺪﻩ ﻛﺎﺭﺕ ﻣﺠﺎﺯ ﺑﻪ ﺍﻧﺠﺎﻡ ﺍﻳﻦ ﺗﺮﺍﻛﻨﺶ ﻧﻴﺴت',
            'mellat_code_412'               => 'ﺷﻨﺎﺳﻪ ﻗﺒﺾ ﻧﺎﺩﺭﺳﺖ ﺍست',
            'mellat_code_413'               => 'ﺷﻨﺎﺳﻪ ﭘﺮﺩﺍﺧﺖ ﻧﺎﺩﺭﺳﺖ ﺍﺳت',
            'mellat_code_414'               => 'ﺳﺎﺯﻣﺎﻥ ﺻﺎﺩﺭ ﻛﻨﻨﺪﻩ ﻗﺒﺾ ﻧﺎﻣﻌﺘﺒﺮ ﺍﺳت',
            'mellat_code_415'               => 'ﺯﻣﺎﻥ ﺟﻠﺴﻪ ﻛﺎﺭﻱ ﺑﻪ ﭘﺎﻳﺎﻥ ﺭﺳﻴﺪﻩ ﺍست',
            'mellat_code_416'               => 'ﺧﻄﺎ ﺩﺭ ﺛﺒﺖ ﺍﻃﻼعات',
            'mellat_code_417'               => 'ﺷﻨﺎﺳﻪ ﭘﺮﺩﺍﺧﺖ ﻛﻨﻨﺪﻩ ﻧﺎﻣﻌﺘﺒﺮ ﺍست',
            'mellat_code_418'               => 'ﺍﺷﻜﺎﻝ ﺩﺭ ﺗﻌﺮﻳﻒ ﺍﻃﻼﻋﺎﺕ ﻣﺸﺘﺮی',
            'mellat_code_419'               => 'ﺗﻌﺪﺍﺩ ﺩﻓﻌﺎﺕ ﻭﺭﻭﺩ ﺍﻃﻼﻋﺎﺕ ﺍﺯ ﺣﺪ ﻣﺠﺎﺯ ﮔﺬﺷﺘﻪ ﺍﺳت',
            'mellat_code_421'               => 'IP نامعتبر است'
        ];
    }
}