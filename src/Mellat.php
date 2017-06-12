<?php

namespace Hossein\Gateway;

class Mellat extends \Hossein\Gateway\Base
{
    /**
     * @var string Mode of connection to bank after starting transaction
     * direct-immediate => default => posting reference id using form immediately printing and submitting
     * direct-delay => posting reference id using form printing and submitting after 3 second, can change by $this->direct_delay
     * indirect => You can get javascript function "mellat_start_payment", printing and call it wherever and whenever you want
     * information => You can get 2 index and implement everything manually, indexes are: index_name, index_value, target means -
     * - you should post index_value under the name of index_name to target
     */
    public $mode;

    /**
     * @var int Number of seconds that direct-delay mode will redirect user after (default: 3)
     */
    public $direct_delay;

    /**
     * @var string Contain TerminalId that given to you by Mellat Bank
     */
    public $terminal;

    /**
     * @var string Contain Username that given to you by Mellat Bank
     */
    public $username;

    /**
     * @var string Contain Password that given to you by Mellat Bank
     */
    public $password;

    /**
     * @var integer The fee that should be payed in Rials
     */
    public $amount;

    /**
     * @var string The URL that will be called after payment (even on success or failure)
     */
    public $callback;

    /**
     * @var integer A unique integer to distinguish each transaction
     */
    public $order_id;

    /**
     * @var string Max length must be 1000 character, send optional data to bank and retrieve from it
     */
    public $additional_data;

    /**
     * @var string URL address for base operational webservice
     */
    private $base_webservice;

    /**
     * @var string URL address for payment operational webservice
     */
    private $payment_webservice;

    /**
     * @var string Reference ID returned by bank in order to start transaction
     */
    private $reference_id;

    /**
     * @var int Response code returned from bank
     */
    private $result_code;

    /**
     * @var string Returned from bank
     */
    private $sale_order_id;

    /**
     * @var string Returned from bank
     */
    private $sale_reference_id;

    /**
     * @var string Returned from bank
     */
    private $card_holder_info;

    /**
     * @var string returned by bank includes first 6 digit and end 4 digit of user bank card
     */
    private $card_holder_pan;


    /**
     * Mellat constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->base_webservice = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
        $this->payment_webservice = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';

        $this->mode = 'direct-immediate';
        $this->direct_delay = 3;
    }


    /**
     * To initiate all values in one request
     * @param string $terminal
     * @param string $username
     * @param string $password
     * @param int $order
     * @param int $amount
     * @param string $callback
     * @param string $additional
     */
    public function init($terminal = null, $username = null, $password = null, $order = null, $amount = 0, $callback = null, $additional = '')
    {
        $this->terminal = $terminal;
        $this->username = $username;
        $this->password = $password;
        $this->amount = $amount;
        $this->callback = $callback;
        $this->order_id = $order;
        $this->additional_data = $additional;
    }

    /**
     * Setting variables individually
     * @param $name variable name
     * @param $value variable value
     * @return bool
     */
    public function __set($name, $value)
    {
        return false;
    }

    /**
     * To get variable value of allowed variable which bank will fill
     * @param $name variable name
     * @return mixed
     */
    public function __get($name)
    {
        if(property_exists($this, $name))
            return $this->$name;
        else
            return null;
    }

    /**
     * Handshake with bank using supplied information to start transaction
     * @throws \Hossein\Gateway\AllException
     */
    public function start_payment()
    {
        $this->validate_data();

        $client = new \nusoap_client($this->base_webservice, 'wsdl');

        if($client->getError())
            throw new AllException('webservice_invalid');

        $parameters = [
            'terminalId'        => $this->terminal,
            'userName'          => $this->username,
            'userPassword'      => $this->password,
            'orderId'           => $this->order_id,
            'amount'            => $this->amount,
            'localDate'         => date('Ymd'),
            'localTime'         => date('His'),
            'additionalData'    => $this->additional_data,
            'callBackUrl'       => $this->callback
        ];

        $start_payment = $client->call('bpPayRequest', $parameters, $this->payment_webservice);

        if($client->fault OR $client->getError())
            throw new AllException('start_payment_invalid');

        if(!isset($start_payment['return']))
            throw new AllException('start_respond_invalid');

        $responses = explode(',', $start_payment['return']);

        $this->result_code = (isset($responses[0]) && strlen($responses[0]) > 0) ? intval($responses[0]) : -1;
        $this->reference_id = (isset($responses[1]) && strlen($responses[1]) > 0) ? trim($responses[1]) : null;

        if($this->result_code !== 0)
            throw new AllException('mellat_code_' . $code);

        $this->posting_reference_id();
    }

    /**
     * Handling all operation needed after payment, just call this after init function when your callback triggered
     * @throws \Hossein\Gateway\AllException
     */
    public function handle_payment()
    {
        $post = $_POST;

        if(isset($post['RefId']))
            $this->reference_id = $post['RefId'];

        if(isset($post['ResCode']))
            $this->result_code = $post['ResCode'];

        if(isset($post['SaleOrderId']))
            $this->sale_order_id = $post['SaleOrderId'];

        if(isset($post['SaleReferenceId']))
            $this->sale_reference_id = $post['SaleReferenceId'];

        if(isset($post['CardHolderInfo']))
            $this->card_holder_info = $post['CardHolderInfo'];

        if(isset($post['CardHolderPan']))
            $this->card_holder_pan = $post['CardHolderPan'];

        if($this->result_code != 0)
            throw new AllException('mellat_code_' . $this->result_code);


        $this->validate_data(true);
        $this->verify_payment();
        $this->settle_payment();
    }

    /**
     * Refund amount to user if it was released from his/her account. Be careful to use it. Maybe just in catch section of callback
     * @return bool refund done or not
     * @throws \Hossein\Gateway\AllException
     */
    public function refund_payment()
    {
        if(!$this->sale_reference_id OR !$this->sale_order_id)
            return true;

        $client = new \nusoap_client($this->base_webservice, 'wsdl');

        if($client->getError())
            throw new AllException('webservice_invalid');

        $parameters = [
            'terminalId'        => $this->terminal,
            'userName'          => $this->username,
            'userPassword'      => $this->password,
            'orderId'           => $this->order_id,
            'saleOrderId'       => $this->sale_order_id,
            'saleReferenceId'   => $this->sale_reference_id
        ];

        $reversal_payment = $client->call('bpReversalRequest', $parameters, $this->payment_webservice);

        if($client->fault OR $client->getError())
            return false;

        if(!isset($reversal_payment['return']) OR strlen($reversal_payment['return']) == 0)
            return false;

        $this->result_code = intval($reversal_payment['return']);

        if($this->result_code !== 0)
            return false;

        return true;
    }

    /**
     * @return array|string javascript function to create and submit form OR array of information OR Echo and submit form
     * Go to $this->mode description
     */
    protected function posting_reference_id()
    {
        switch ($this->mode)
        {
            case 'direct-immediate':
                $result = '<script type="text/javascript">';
                $result .= $this->form_generator();
                $result .= 'document.addEventListener("DOMContentLoaded", function(){mellat_start_payment();}, false);';;
                $result .= '</script>';

                echo $result;
                break;

            case 'direct-delay';
                $result = '<script type="text/javascript">';
                $result .= $this->form_generator();
                $result .= 'document.addEventListener("DOMContentLoaded", function(){setTimeout(function(){mellat_start_payment()}, ' . $this->direct_delay . '}, false));';
                $result .= '</script>';

                echo $result;
                break;

            case 'indirect':
                return $this->form_generator();
                break;

            case 'information':
                return [
                    'index_name'    => 'RefId',
                    'index_value'   => $this->reference_id,
                    'target'        => $this->payment_webservice
                ];
                break;

            default:
                $this->mode = 'direct-immediate';
                $this->posting_reference_id();
                break;
        }
    }

    /**
     * Verify request according to posted data from bank
     * @throws \Hossein\Gateway\AllException
     */
    protected function verify_payment()
    {
        $client = new \nusoap_client($this->base_webservice, 'wsdl');

        if($client->getError())
            throw new AllException('webservice_invalid');

        $parameters = [
            'terminalId'        => $this->terminal,
            'userName'          => $this->username,
            'userPassword'      => $this->password,
            'orderId'           => $this->order_id,
            'saleOrderId'       => $this->sale_order_id,
            'saleReferenceId'   => $this->sale_reference_id
        ];

        $verify_payment = $client->call('bpVerifyRequest', $parameters, $this->payment_webservice);

        if($client->fault OR $client->getError())
            throw new AllException('verify_payment_invalid');

        if(!isset($verify_payment['return']) OR strlen($verify_payment['return']) == 0)
        {
            $this->inquiry_payment();
            $this->settle_payment();
        }

        $this->result_code = intval($verify_payment['return']);

        if(!in_array($this->result_code, array(0, 43)))
            throw new AllException('mellat_code_' . $this->result_code);
    }

    /**
     * Settle request according to posted data from bank
     * @throws \Hossein\Gateway\AllException
     */
    protected function settle_payment()
    {
        $client = new \nusoap_client($this->base_webservice, 'wsdl');

        if($client->getError())
            throw new AllException('webservice_invalid');

        $parameters = [
            'terminalId'        => $this->terminal,
            'userName'          => $this->username,
            'userPassword'      => $this->password,
            'orderId'           => $this->order_id,
            'saleOrderId'       => $this->sale_order_id,
            'saleReferenceId'   => $this->sale_reference_id
        ];

        $settle_payment = $client->call('bpSettleRequest', $parameters, $this->payment_webservice);

        if($client->fault OR $client->getError())
            throw new AllException('verify_payment_invalid');

        if(!isset($settle_payment['return']) OR strlen($settle_payment['return']) == 0)
            $this->inquiry_payment();

        $this->result_code = intval($settle_payment['return']);

        if(!in_array($this->result_code, array(0, 45)))
            throw new AllException('mellat_code_' . $this->result_code);
    }

    /**
     * Inquiry request according to posted data from bank if verification failed
     * @throws \Hossein\Gateway\AllException
     */
    protected function inquiry_payment()
    {
        $client = new \nusoap_client($this->base_webservice, 'wsdl');

        if($client->getError())
            throw new AllException('webservice_invalid');

        $parameters = [
            'terminalId'        => $this->terminal,
            'userName'          => $this->username,
            'userPassword'      => $this->password,
            'orderId'           => $this->order_id,
            'saleOrderId'       => $this->sale_order_id,
            'saleReferenceId'   => $this->sale_reference_id
        ];

        $inquiry_payment = $client->call('bpInquiryRequest ', $parameters, $this->payment_webservice);

        if($client->fault OR $client->getError())
            throw new AllException('verify_payment_invalid');

        if(!isset($inquiry_payment['return']) OR strlen($inquiry_payment['return']) == 0)
            throw new AllException('verify_respond_invalid');

        $this->result_code = intval($inquiry_payment['return']);

        if($this->result_code !== 0)
            throw new AllException('mellat_code_' . $this->result_code);
    }

    /**
     * @param bool $handle Validating data for starting payment(false) or to handling payment(true)
     * @return bool to check all are ready to start transaction or not
     * @throws \Hossein\Gateway\AllException
     */
    protected function validate_data($handle = false)
    {
        if(!$this->terminal)
            throw new AllException('api_absence');
        elseif(!$this->username)
            throw new AllException('username_absence');
        elseif(!$this->password)
            throw new AllException('password_absence');
        elseif(!$handle && !$this->amount)
            throw new AllException('amount_absence');
        elseif(!$handle && (!is_integer($this->amount) OR $this->amount <= 0))
            throw new AllException('amount_invalid');
        elseif(!$handle && !$this->callback)
            throw new AllException('callback_absence');
        elseif(!$handle && !$this->valid_url($this->callback))
            throw new AllException('callback_invalid');
        elseif(!$this->order_id)
            throw new AllException('order_id_absence');
        elseif(!is_integer($this->order_id))
            throw new AllException('order_id_invalid');

        return true;
    }

    /**
     * @return string Javascript function to create and submit form => 'mellat_start_payment'
     */
    private function form_generator()
    {
        $form  = 'function mellat_start_payment(){';
        $form .= 'var form = document.createElement("form");';
        $form .= 'form.setAttribute("method", "POST");';
        $form .= 'form.setAttribute("action", "' . $this->payment_webservice . '");';
        $form .= 'form.setAttribute("target", "_self");';
        $form .= 'var hidden_field = document.createElement("input"); ';
        $form .= 'hidden_field.setAttribute("name", "RefId");';
        $form .= 'hidden_field.setAttribute("value", "' . $this->reference_id . '");';
        $form .= 'form.appendChild(hidden_field);';
        $form .= 'document.body.appendChild(form);';
        $form .= 'form.submit();';
        $form .= 'document.body.removeChild(form);';
        $form .= '}';

        return $form;
    }
}