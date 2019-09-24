<?php

namespace Alchemy\SkeletonPlugin\Security;

use Alchemy\Phrasea\Authorization\BaseVoter;
use Alchemy\Phrasea\Model\Repositories\UserRepository;
use Alchemy\Phrasea\Model\Entities\User;

class PluginConfigurationVoter extends BaseVoter
{
    const VIEW = 'view';

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct(
            $userRepository,
            [self::VIEW],
            [
                'Alchemy\SkeletonPlugin\Configuration\ConfigurationTab',
            ]
        );
    }

    protected function isGranted($attribute,
        /** @noinspection PhpUnusedParameterInspection */
                                 $tab,
        /** @noinspection PhpUnusedParameterInspection */
                                 User $user = null)
    {
        switch ($attribute) {
            case self::VIEW:
                return true;
        }

        return false;
    }
}
