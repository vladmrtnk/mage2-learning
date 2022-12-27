<?php

namespace Elogic\SalesforceIntegration\Helper;

use Elogic\SalesforceIntegration\Helper\Salesforce as SalesforceHelper;
use Laminas\View\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl as MagentoCurl;


class Curl extends AbstractHelper
{
    /**
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        MagentoCurl $curl
    ) {
        $this->curl = $curl;
    }

    /**
     * @return mixed
     */
    public function getSalesforceToken()
    {
        $this->curl->post(
            'https://login.salesforce.com/services/oauth2/token',
            SalesforceHelper::ACCESS,
        );

        $result = json_decode($this->curl->getBody())->access_token;

        return $result;

    }

    public function setHeader($bearerToken)
    {
        $this->curl->setHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ]);
    }
}