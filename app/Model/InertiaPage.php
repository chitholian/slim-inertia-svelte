<?php

namespace App\Model;

class InertiaPage implements \JsonSerializable
{
    private string $component, $url, $version;
    private array $props;

    public function __construct(string $component, string $url, string $version, array $props = [])
    {
        $this->component = $component;
        $this->props = $props;
        $this->url = $url;
        $this->version = $version;
    }

    public function setProps(array $props): self
    {
        $this->props = $props;
        return $this;
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public function jsonSerialize(): array
    {
        array_walk($this->props, function (&$prop) {
            if ($prop instanceof \Closure) {
                $prop = $prop();
            }
        });
        return [
            'component' => $this->component,
            'props' => $this->props,
            'url' => $this->url,
            'version' => $this->version,
        ];
    }
}
