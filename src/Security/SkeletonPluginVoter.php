<?php

namespace Alchemy\SkeletonPlugin\Security;

use Alchemy\Phrasea\Authorization\BaseVoter;
use Alchemy\Phrasea\Model\Entities\User;
use Silex\Application;

class SkeletonPluginVoter extends BaseVoter
{
    const VIEW = 'view';

    private $application;

    public function __construct(Application $app)
    {
        parent::__construct(
            $app['repo.users'],
            [self::VIEW],
            [
                'Alchemy\SkeletonPlugin\ActionBar\ActionBarPlugin',
                'Alchemy\SkeletonPlugin\BasketActionBar\BasketActionBarPlugin',
            ]
        );

        $this->application = $app;
    }


    protected function isGranted($attribute,
        /** @noinspection PhpUnusedParameterInspection */
                                 $plugin,
        /** @noinspection PhpUnusedParameterInspection */
                                 User $user = null)
    {
        switch ($attribute) {
            case self::VIEW:
                if ($this->application['plugin.skeleton.config']) {
                    return true;
                }

                return false;
        }

        return false;
    }
}
