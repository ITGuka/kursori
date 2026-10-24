<?php
declare(strict_types=1);

namespace Core\View;

use RuntimeException;
use Throwable;

/**
 * ერთი შაბლონის რენდერი. შაბლონში $this ეს ობიექტია:
 *   <?php $this->layout('front/layouts/main', ['title' => '...']) ?>
 *   <?php $this->start('scripts') ?> ... <?php $this->stop() ?>
 *   <?= $this->section('content') ?>   <?= $this->insert('front/partials/nav') ?>
 */
final class Template
{
    private ?string $layout = null;
    private array $layoutData = [];
    /** @var array<string, string> */
    private array $sections = [];
    /** @var list<string> */
    private array $open = [];

    public function __construct(
        private readonly View $view,
        private readonly string $name,
        private readonly array $data,
    ) {
    }

    public function render(): string
    {
        $content = $this->capture();
        if ($this->open !== []) {
            throw new RuntimeException("Section [{$this->open[0]}] in view [{$this->name}] is not closed.");
        }
        if ($this->layout === null) {
            return $content;
        }

        $layout = new self($this->view, $this->layout, $this->layoutData + $this->data);
        // ჩადგმულ layout-ში content არის შიდა layout-ის შედეგი
        $layout->sections = ['content' => $content] + $this->sections;
        return $layout->render();
    }

    public function layout(string $name, array $data = []): void
    {
        $this->layout = $name;
        $this->layoutData = $data;
    }

    public function start(string $name): void
    {
        $this->open[] = $name;
        ob_start();
    }

    /** დახურული სექცია ემატება იმავე სახელის არსებულს (მაგ. რამდენიმე "scripts") */
    public function stop(): void
    {
        $name = array_pop($this->open) ?? throw new RuntimeException('stop() called without start().');
        $this->sections[$name] = ($this->sections[$name] ?? '') . ob_get_clean();
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    public function insert(string $name, array $data = []): string
    {
        return (new self($this->view, $name, $data + $this->data))->render();
    }

    public function e(mixed $value): string
    {
        return e($value);
    }

    private function capture(): string
    {
        $level = ob_get_level();
        ob_start();
        try {
            // ცალკე scope: შაბლონში ჩანს მხოლოდ $this და გადაცემული ცვლადები
            (function (): void {
                extract(func_get_arg(1), EXTR_SKIP);
                include func_get_arg(0);
            })->call($this, $this->view->file($this->name), $this->data);
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        return (string)ob_get_clean();
    }
}
