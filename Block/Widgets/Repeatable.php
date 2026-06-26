<?php
declare(strict_types=1);

namespace MageOS\AdvancedWidget\Block\Widgets;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\DataObject;

class Repeatable extends AbstractColumns implements BlockInterface
{

    /**
     * Fetches `conditions` containing serialized items then turns them into DataObjects
     *
     * @return array
     */
    public function getRepeatableFields(): array
    {
        $content = $this->getConditions();

        if ($content && is_array($content)) {
            return array_map(
                function ($data) {
                    return new DataObject($data);
                },
                $content
            );
        }

        return $content;
    }
}
