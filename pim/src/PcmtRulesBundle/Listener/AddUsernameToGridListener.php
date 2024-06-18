<?php

declare(strict_types=1);

namespace PcmtRulesBundle\Listener;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddUsernameToGridListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onBuildAfter(BuildAfter $event): void
    {
        $dataSource = $event->getDatagrid()->getDatasource();

        $token = $this->tokenStorage->getToken();
        /** @var User $user */
        $user = null !== $token ? $token->getUser() : null;

        $parameters = $dataSource->getParameters();
        $parameters['user'] = $user ? $user->getUsername() : null;
        $dataSource->setParameters($parameters);

        /** @var QueryBuilder $qb */
        $qb = $dataSource->getQueryBuilder();

        $role = $user ? $user->getRole('ROLE_ADMINISTRATOR') : null;
        if (!$role) {
            $qb->andWhere($qb->expr()->eq('e.user', ':user'));
        } else {
            // admin also sees jobs run by no user
            $qb->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->eq('e.user', ':user'),
                    $qb->expr()->isNull('e.user')
                )
            );
        }
    }
}
