<?php

namespace Atipik\RedmineReminder;

use Atipik\RedmineReminder\Redmine\Request;

class Bot {
    protected $config;

    public function __construct($configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Exception('Config file "' . $configFile . '" does not exist.');
        }

        if (!is_readable($configFile)) {
            throw new \Exception('Config file "' . $configFile . '" is not readable.');
        }

        $this->config = json_decode(file_get_contents($configFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Config file "' . $configFile . '" is not valid.');
        }

        $this->request = new Request(
            $this->config['redmineBaseUri'],
            $this->config['redmineApiKey']
        );
    }

    public function run()
    {
        $users = $this->getUsers();

        $this->writeln('%d user(s) found', count($users));

        foreach ($users as $user) {
            $this->writeln();
            $this->writeln('User "#%d %s"', $user['id'], $user['login']);

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
        return $this->request->all('users');
    }

    protected function getIssuesToBeNotified(array $user)
    {
        return $this->request->all(
            'issues',
            array(
                'assigned_to_id' => $user['id'],
                'status_id'      => 7,              // en attente
            )
        );
    }

    protected function sendMail(array $user, array $issues)
    {
        if ($issues) {
            $text = array();

            $text[] = sprintf(
                'Bonjour %s,',
                $user['firstname']
            );

            $text[] = '';

            if (count($issues) === 1) {
                $text[] = 'Vous avez 1 ticket Redmine affecté et ouvert:';
            } else {
                $text[] = sprintf(
                    'Vous avez %d tickets Redmine affectés et ouverts:',
                    count($issues)
                );
            }

            $idLength  = strlen($issues[0]['id']);
            $padLength = strlen(rtrim($this->config['redmineBaseUri'], '/')) + $idLength + 8;

            foreach ($issues as $issue) {
                $text[] = sprintf(
                    '* [#%0' . $idLength . 'd] %s - %s',
                    $issue['id'],
                    str_pad(
                        sprintf(
                            '%s/issues/%d',
                            rtrim($this->config['redmineBaseUri'], '/'),
                            $issue['id']
                        ),
                        $padLength
                    ),
                    $issue['subject']
                );
            }

            $text = implode(PHP_EOL, $text);

            mail($user['mail'], 'Reminder Redmine', $text);

            $this->writeln('Send 1 mail.');
        } else {
            $this->writeln('Send 0 mail.');
        }
    }
}