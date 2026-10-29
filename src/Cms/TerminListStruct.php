<?php declare(strict_types=1);

namespace Nova\Termine\Cms;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Daten des Erlebniswelten-Elements „Terminliste“ für das Storefront-Template.
 */
class TerminListStruct extends Struct
{
    /**
     * @param list<array<string, mixed>> $booth  Termine „Hier sind wir dabei“
     * @param list<array<string, mixed>> $scene  Termine „Weitere Treffen in der Szene“
     * @param list<string>               $jsonLd schema.org-Daten (bereits sicher kodiert)
     */
    public function __construct(
        protected array $booth = [],
        protected array $scene = [],
        protected array $jsonLd = [],
        protected string $area = 'all',
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function getBooth(): array
    {
        return $this->booth;
    }

    /** @return list<array<string, mixed>> */
    public function getScene(): array
    {
        return $this->scene;
    }

    /** @return list<string> */
    public function getJsonLd(): array
    {
        return $this->jsonLd;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function isEmpty(): bool
    {
        return $this->booth === [] && $this->scene === [];
    }

    public function getApiAlias(): string
    {
        return 'nova_termine_list';
    }
}
