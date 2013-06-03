<?php

namespace Dumper;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class CustomLoggingFileDumper extends FileDumper
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher, $outputPath)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($outputPath);
    }

    public function log($message)
    {
        $event = new GenericEvent($this, array('message' => $message));
        $this->eventDispatcher->dispatch('file_dumper.log', $event);
    }

    public function logSection($section, $message)
    {
        $event = new GenericEvent($this, array('section' => $section, 'message' => $message));
        $this->eventDispatcher->dispatch('file_dumper.log_section', $event);
    }
}
