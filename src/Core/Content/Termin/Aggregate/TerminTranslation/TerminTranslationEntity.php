<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation;

use Nova\Termine\Core\Content\Termin\TerminEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class TerminTranslationEntity extends TranslationEntity
{
    protected string $novaTerminId = '';

    protected ?string $title = null;

    protected ?string $program = null;

    protected ?TerminEntity $novaTermin = null;

    public function getNovaTerminId(): string
    {
        return $this->novaTerminId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function getNovaTermin(): ?TerminEntity
    {
        return $this->novaTermin;
    }
}
