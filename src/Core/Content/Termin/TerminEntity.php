<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin;

use Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation\TerminTranslationCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

class TerminEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $title = null;

    protected ?string $program = null;

    protected string $location = '';

    protected ?string $country = null;

    protected ?\DateTimeInterface $startDate = null;

    protected ?\DateTimeInterface $endDate = null;

    protected ?string $url = null;

    protected string $participation = TerminDefinition::PARTICIPATION_OPEN;

    protected string $status = TerminDefinition::STATUS_ACTIVE;

    protected ?string $mediaId = null;

    protected ?MediaEntity $media = null;

    protected ?string $internalNote = null;

    protected ?string $reporterEmail = null;

    protected ?TerminTranslationCollection $translations = null;

    protected ?SalesChannelCollection $salesChannels = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getParticipation(): string
    {
        return $this->participation;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function getInternalNote(): ?string
    {
        return $this->internalNote;
    }

    public function getReporterEmail(): ?string
    {
        return $this->reporterEmail;
    }

    public function getTranslations(): ?TerminTranslationCollection
    {
        return $this->translations;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }
}
