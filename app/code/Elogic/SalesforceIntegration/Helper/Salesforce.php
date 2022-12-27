<?php

namespace Elogic\SalesforceIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Salesforce extends AbstractHelper
{
    public const ACCESS = [
        'grant_type'    => 'password',
        'client_id'     => '3MVG9vvlaB0y1YsKrIDS1qTLwTNff8.zV18wa7Md3y8psKO4bya_GUFftLZt.H68cfNgY0jm.Ich4BGmwMVy4',
        'client_secret' => 'A1A15760866055F21DAFFFCA29E3D5D75CE66F4A57874C6C7953E41F50F52694',
        'username'      => 'admin@abobus228.bom',
        'password'      => '6Ev8nD4u6RCfcwM',
    ];
    public const API_URL = 'https://abobus228-dev-ed.develop.my.salesforce.com';
    public const PRICEBOOK_ID = '01s68000000CuhyAAC';
}
