<?php

namespace Atipik\RedmineReminder;

use \User;
use \Issue;

use Trucker\Facades\Config as TruckerConfig;
use Trucker\Facades\ConditionFactory;

class Bot {
    protected $config;

    public function __construct($configFile)
    {
        $this->config = json_decode(file_get_contents($configFile), true);

        TruckerConfig::set('auth.driver',         'basic');
        TruckerConfig::set('auth.basic.username', $this->config['redmineApiKey']);
        TruckerConfig::set('auth.basic.password', uniqid());
        TruckerConfig::set('request.base_uri',    $this->config['redmineBaseUri']);
        TruckerConfig::set('request.driver',      'json_rest');
    }

    public function run()
    {
        $users = $this->getUsers();

        $this->writeln('%d user(s) found', count($users));

        foreach ($users as $user) {
            $this->writeln();
            $this->writeln('User "%s"', $user);

            $issues = $this->getIssuesToBeNotified($user);
            $this->writeln('%d issue(s) found', count($issues));

            $this->sendMail($user, $issues);
        }
    }

    protected function writeln()
    {
        $format = '';
        $args   = array();

        if (func_num_args() > 0) {
            $format = func_get_arg(0);
        }

        if (func_num_args() > 1) {
            $args = func_get_args();

            array_shift($args);
        }

        $format .= PHP_EOL;

        echo vsprintf($format, $args);
    }

    protected function getUsers()
    {
        return User::all();
    }

    protected function getIssuesToBeNotified(User $user)
    {
        $conditions = ConditionFactory::build();
        $conditions->addCondition('assigned_to_id', '=', $user->getId());

        return Issue::all($conditions);
    }

    protected function sendMail(User $user, array $issues)
    {
        $this->writeln('Send 0 mail.');
    }
}