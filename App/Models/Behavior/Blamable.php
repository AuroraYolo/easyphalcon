<?php


namespace App\Models\Behavior;


use App\Component\Dev\Log;

use App\Component\Enum\Services;
use App\Component\User\Service;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

class Blamable extends Behavior implements BehaviorInterface
{
    public function notify($eventType, ModelInterface $model)
    {
        switch ($eventType) {
            case 'afterCreate':
            case 'afterDelete':
            case 'afterUpdate':
                $log = Log::logger();
                /** @var Service $userService */
                $userService = $this->getDI()->get(Services::USER_SERVICE);
                $userName = $userService->getDetails()->name;
                $log->info($userName . ' ' . $eventType . ' ' . $model->id);
                break;
            default:
                /* ignore the rest of events */
        }
    }
}