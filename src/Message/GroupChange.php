<?php
/**
 * Created by PhpStorm.
 * User: Hanson
 * Date: 2017/2/12
 * Time: 20:44.
 */

namespace Hanson\Vbot\Message;

class GroupChange extends Message implements MessageInterface
{
    const TYPE = 'group_change';

    public $action;

    public $inviter;

    public $invited;

    public function make($msg)
    {
        return $this->getCollection($msg, static::TYPE);
    }

    protected function afterCreate()
    {
        if (str_contains($this->message, '邀请你')) {
            $this->action = 'INVITE';
        } elseif (! empty($parseInfo = $this->parseJoinMessage($this->message))) {
            $this->inviter = $parseInfo['inviter'];
            $this->invited = $parseInfo['invited'];
            $this->action = 'ADD';
        } elseif (str_contains($this->message, '移出了群聊')) {
            $this->action = 'REMOVE';
        } elseif (str_contains($this->message, '改群名为')) {
            $this->action = 'RENAME';
        } elseif (str_contains($this->message, '移出群聊')) {
            $this->action = 'BE_REMOVE';
        }
    }

    protected function parseJoinMessage($message): array
    {
        switch ($message) {
            case preg_match('/"?(.+)"?邀请"(.+)"加入了群聊/', $message, $match):
                $parseInfo = ['inviter' => $match[1], 'invited' => $match[2]];
                break;
            case preg_match('/"(.+)"通过扫描"?(.+)"?分享的二维码加入群聊/', $message, $match):
                $parseInfo = ['inviter' => $match[2], 'invited' => $match[1]];
                break;
            case preg_match('/"?(.+)"?通过"(.+)"加入群聊/', $message, $match):
                $parseInfo = ['inviter' => $match[2], 'invited' => $match[1]];
                break;
            default:
                $parseInfo = [];
                break;
        }

        return $parseInfo;
    }

    protected function getExpand(): array
    {
        return ['action' => $this->action, 'inviter' => $this->inviter, 'invited' => $this->invited];
    }

    protected function parseToContent(): string
    {
        return $this->message;
    }
}
