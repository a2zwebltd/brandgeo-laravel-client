<?php

namespace A2ZWeb\BrandGeoClient\Resources;

use A2ZWeb\BrandGeoClient\Data\Account;

class AccountResource extends Resource
{
    /**
     * The authenticated account with subscription status, plan quota and usage.
     */
    public function get(): Account
    {
        return Account::fromArray($this->client->get('account')['data']);
    }
}
