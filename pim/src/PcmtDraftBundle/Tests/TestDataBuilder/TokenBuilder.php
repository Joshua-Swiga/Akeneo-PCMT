<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\UserInterface;
use FOS\OAuthServerBundle\Model\Token;

class TokenBuilder
{
    /** @var Token */
    private $token;

    public function __construct()
    {
        $this->token = new Token();
    }

    public function withUser(UserInterface $user): self
    {
        $this->token->setUser($user);

        return $this;
    }

    public function build(): Token
    {
        return $this->token;
    }
}