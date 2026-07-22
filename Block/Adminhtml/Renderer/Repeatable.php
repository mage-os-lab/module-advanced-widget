<?php
declare(strict_types=1);

namespace MageOS\AdvancedWidget\Block\Adminhtml\Renderer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface as FormElementRenderer;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\Store;

class Repeatable extends Template implements FormElementRenderer
{

    /**
     * @var string
     */
    protected $_template = 'MageOS_AdvancedWidget::widget/field/rows/renderer.phtml';

    /**
     * @var string
     */
    protected string $_repeaterTemplate = 'MageOS_AdvancedWidget::widget/field/rows/renderer/repeater-template.phtml';

    /**
     * @var string
     */
    protected string $_repeaterEditModalTemplate = 'MageOS_AdvancedWidget::widget/field/rows/renderer/repeater-edit-modal-template.phtml';

    /**
     * @param Context $context
     * @param Http $request
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $productCollectionFactory
     * @param ImageHelper $imageHelper
     * @param array $customFields
     * @param array $data
     */
    public function __construct(
        protected Context $context,
        protected Http $request,
        protected ProductRepositoryInterface $productRepository,
        protected CollectionFactory $productCollectionFactory,
        protected ImageHelper $imageHelper,
        protected JsonSerializer $jsonSerializer,
        protected AssetRepository $assetRepository,
        protected array $customFields = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $this->element = $element;
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getCustomFields() {
        return $this->customFields;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAddButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Add Row'),
                'onclick' => 'return window.' . $this->getElement()->getHtmlId() . 'RowsControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_row_item_button');
        return $button->toHtml();
    }

    /**
     * @return string
     */
    public function getUploadButtonOnClickActionUrl(): string
    {
        return $this->getUrl(
            'cms/wysiwyg_images/index',
            ['target_element_id' => '__target_element_id__', 'type' => 'file']
        );
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(): string
    {
        return $this->_storeManager->getStore(Store::DEFAULT_STORE_ID)
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return AbstractElement
     */
    public function getElement(): AbstractElement
    {
        return $this->element;
    }

    /**
     * @return mixed|string
     */
    public function getParameterName()
    {
        $parameterName = (string)$this->element->getName();
        if (preg_match('/\[(.*?)\]/', $parameterName, $matches) && isset($matches[1])) {
            $parameterName = $matches[1];
        }

        return $parameterName;
    }

    /**
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getValues()
    {
        if (is_array($this->getElement()->getValue())) {
            $rows = $this->getElement()->getValue();
            foreach ($rows as $index => $row) {
                foreach ($row as $key => $value) {
                    if (isset($row[$key]) && $row[$key] !== "" && strpos($key, "product") === 0) {
                        try {
                            $product = $this->productRepository->getById((int)$row[$key]);
                            $rows[$index][$key . "_sku"] = $product->getSku();
                            $rows[$index][$key . "_name"] = $product->getName();
                            $rows[$index][$key . "_image"] = $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl();
                        } catch (\Exception $e) {
                            unset($rows[$index]);
                        }
                    }
                }
            }
            $values = json_encode($rows);
            return json_decode($values, true) ?: [];
        } else {
            $values = str_replace("'", '"', (string)$this->getElement()->getValue());
            return json_decode(str_replace("&#039;", '"', $values), true) ?: [];
        }
    }

    /**
     * @param $fieldName
     * @param $type
     * @param $parameterName
     * @param $htmlId
     * @param $htmlName
     * @param $data
     * @param bool $isModal
     * @return string
     * @throws LocalizedException
     */
    public function addCustomField($fieldName, $type, $parameterName, $htmlId, $htmlName, $data, bool $isModal = false): string
    {
        foreach ($this->customFields as $fieldData) {
            if ($fieldData['type'] === $type) {
                return $this->getLayout()->createBlock(
                    Template::class
                )->setTemplate(
                    $fieldData['template']
                )->setData(
                    'fieldName', $fieldName
                )->setData(
                    'parameterName', $parameterName
                )->setData(
                    'htmlId', $htmlId
                )->setData(
                    'fieldData', $data
                )->setData(
                    'htmlName', $htmlName
                )->setData(
                    'isModal', $isModal
                )->toHtml();
            }
        }
        return '';
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getRepeaterTemplate($element, $fields, $parameterName)
    {
        $repeaterTemplate = $this->getLayout()->createBlock(
            $this::class
        )->setTemplate(
            $this->_repeaterTemplate
        )->setData(
            'element', $element
        )
        ->setData(
            'fields', $fields
        )
        ->setData(
            'parameterName', $parameterName
        );
        return str_replace(["\r\n", "\n", "\r"], '', $repeaterTemplate->toHtml());
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getRepeaterEditModalTemplate($element, $fields, $parameterName)
    {
        $repeaterEditModalTemplate = $this->getLayout()->createBlock(
            $this::class
        )->setTemplate(
            $this->_repeaterEditModalTemplate
        )->setData(
            'element', $element
        )
        ->setData(
            'fields', $fields
        )
        ->setData(
            'parameterName', $parameterName
        );
        return str_replace(["\r\n", "\n", "\r"], '', $repeaterEditModalTemplate->toHtml());
    }

    /**
     * @param $block
     * @param $id
     * @return mixed
     * @throws LocalizedException
     */
    public function getMainFormHtml($block, $id)
    {
        return $this->getLayout()->createBlock(
            $block::class
        )->getMainFormHtml($id);
    }

    public function getWysiwygConfig(): bool|string
    {
        $config =  [
            'turn_on' => false,
            'turn_on_modal' => false,
            'height' => "300px",
            'plugins' => [],
            'tinymce' => [
                'toolbar' => 'undo redo | styleselect | fontsizeselect | lineheight | forecolor backcolor | bold italic underline | alignleft aligncenter alignright | numlist bullist',
                'plugins' => implode(
                    ' ',
                    [
                        'advlist',
                        'lists',
                    ]
                ),
                'content_css' => [
                    $this->assetRepository->getUrl('mage/adminhtml/wysiwyg/tiny_mce/themes/ui.css'),
                    $this->assetRepository->getUrl('Magento_PageBuilder::css/source/form/element/tinymce.css')
                ]
            ],
        ];

        return $this->jsonSerializer->serialize($config);
    }
}
