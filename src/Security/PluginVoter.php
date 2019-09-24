<?php

namespace Alchemy\SkeletonPlugin\Security;

use Alchemy\Phrasea\Authorization\BaseVoter;
use Alchemy\Phrasea\Model\Entities\User;
use Alchemy\Phrasea\Model\Repositories\UserRepository;
use Silex\Application;

class PluginVoter extends BaseVoter
{
    const VIEW = 'view';

    private $configuration;

    public function __construct(UserRepository $userRepository, $configuration)
    {
        parent::__construct(
            $userRepository,
            [self::VIEW],
            [
                'Alchemy\SkeletonPlugin\ActionBarPlugin',
                'Alchemy\SkeletonPlugin\BasketActionBarPlugin',
                'Alchemy\SkeletonPlugin\WorkZonePlugin',
            ]
        );

        $this->configuration  = $configuration;
    }


    protected function isGranted($attribute,
        /** @noinspection PhpUnusedParameterInspection */
                                 $plugin,
        /** @noinspection PhpUnusedParameterInspection */
                                 User $user = null)
    {
        switch ($attribute) {
            case self::VIEW:
                if ($this->configuration) {
                    return true;
                }

                return false;
        }

        return false;
    }
}
