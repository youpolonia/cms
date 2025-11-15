<?php

class ModerationOverdueNotification
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
        $html = View::render('emails/moderation/overdue', [
            'item' => $this->item,
            'hoursOverdue' => $this->hoursOverdue
        ]);

        $subject = 'Moderation Item Overdue - 24 Hour Warning';

        return Mailer::send($to, $subject, $html);
    }
}
