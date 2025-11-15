<?php

class ModerationEscalationNotification
{
    protected $item;
    protected $hoursOverdue;

    public function __construct($item)
    {
        $this->item = $item;
        $this->hoursOverdue = (new DateTime())->diff($item->received_at)->h;
    }

    public function send($to)
    {
        $html = View::render('emails/moderation/escalation', [
            'item' => $this->item,
            'hoursOverdue' => $this->hoursOverdue
        ]);

        $subject = 'Moderation Item Escalation - 48 Hour Warning';

        return Mailer::send($to, $subject, $html);
    }
}
