<?php
/**
 * @var Core\View\Template $this
 * @var string $header პირველი header ბლოკის HTML
 * @var string $content დანარჩენი ბლოკები
 */
$this->layout('front/layouts/main');
$this->start('header');
echo $header;
$this->stop();
echo $content;
