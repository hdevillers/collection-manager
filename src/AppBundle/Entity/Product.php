<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product.
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 * @UniqueEntity({"brandReference", "brand", "team"}, message="A product already exist with the brand reference: {{ value }}.")
 * @UniqueEntity({"sellerReference", "seller", "seller"}, message="A product already exist with the seller reference: {{ value }}.")
 */
class Product
{
    const NUM_ITEMS = 10;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"}, unique=false)
     * @ORM\Column(name="slug", type="string", length=128)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Brand", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="brandReference", type="string", length=255)
     */
    private $brandReference;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Seller", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seller;

    /**
     * @var string
     *
     * @ORM\Column(name="sellerReference", type="string", length=255)
     */
    private $sellerReference;

    /**
     * @var int
     *
     * @ORM\Column(name="catalogPrice", type="integer")
     */
    private $catalogPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="negotiatedPrice", type="integer")
     */
    private $negotiatedPrice;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\DocumentFile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $quoteFile;
    private $addQuoteFile = false;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\DocumentFile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $manualFile;
    private $addManualFile = false;

    /**
     * @var string
     *
     * @ORM\Column(name="packedBy", type="integer")
     */
    private $packedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="packagingUnit", type="string", length=255)
     */
    private $packagingUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="storageUnit", type="string", length=255)
     */
    private $storageUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="stock", type="integer")
     */
    private $stock;

    /**
     * @var int
     *
     * @ORM\Column(name="stockWarningAlert", type="integer")
     */
    private $stockWarningAlert;

    /**
     * @var int
     *
     * @ORM\Column(name="stockDangerAlert", type="integer")
     */
    private $stockDangerAlert;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductMovement", mappedBy="product")
     */
    private $movements;

    public function __construct()
    {
        $this->stock = 0;
        $this->movements = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set location.
     *
     * @param Location $location
     *
     * @return Product
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set brand.
     *
     * @param Brand $brand
     *
     * @return Product
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand.
     *
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set brandReference.
     *
     * @param string $brandReference
     *
     * @return Product
     */
    public function setBrandReference($brandReference)
    {
        $this->brandReference = $brandReference;

        return $this;
    }

    /**
     * Get brandReference.
     *
     * @return string
     */
    public function getBrandReference()
    {
        return $this->brandReference;
    }

    /**
     * Set seller.
     *
     * @param Seller $seller
     *
     * @return Product
     */
    public function setSeller(Seller $seller)
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Get seller.
     *
     * @return Seller
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Set sellerReference.
     *
     * @param string $sellerReference
     *
     * @return Product
     */
    public function setSellerReference($sellerReference)
    {
        $this->sellerReference = $sellerReference;

        return $this;
    }

    /**
     * Get sellerReference.
     *
     * @return string
     */
    public function getSellerReference()
    {
        return $this->sellerReference;
    }

    /**
     * Set catalogPrice.
     *
     * @param int $catalogPrice
     *
     * @return Product
     */
    public function setCatalogPrice($catalogPrice)
    {
        $this->catalogPrice = $catalogPrice;

        return $this;
    }

    /**
     * Get catalogPrice.
     *
     * @return int
     */
    public function getCatalogPrice()
    {
        return $this->catalogPrice;
    }

    /**
     * Set negotiatedPrice.
     *
     * @param int $negotiatedPrice
     *
     * @return Product
     */
    public function setNegotiatedPrice($negotiatedPrice)
    {
        $this->negotiatedPrice = $negotiatedPrice;

        return $this;
    }

    /**
     * Get negotiatedPrice.
     *
     * @return int
     */
    public function getNegotiatedPrice()
    {
        return $this->negotiatedPrice;
    }

    /**
     * Set quote file.
     *
     * @param DocumentFile $quoteFile
     *
     * @return Product
     */
    public function setQuoteFile(DocumentFile $quoteFile)
    {
        $this->quoteFile = $quoteFile;

        return $this;
    }

    /**
     * Get quote file.
     *
     * @return DocumentFile
     */
    public function getQuoteFile()
    {
        return $this->quoteFile;
    }

    /**
     * Set addQuoteFile.
     *
     * @param string $addQuoteFile
     *
     * @return Product
     */
    public function setAddQuoteFile($addQuoteFile)
    {
        $this->addQuoteFile = $addQuoteFile;

        return $this;
    }

    /**
     * Get addQuoteFile.
     *
     * @return string
     */
    public function getAddQuoteFile()
    {
        return $this->addQuoteFile;
    }

    /**
     * Set manual file.
     *
     * @param DocumentFile $manualFile
     *
     * @return Product
     */
    public function setManualFile(DocumentFile $manualFile)
    {
        $this->manualFile = $manualFile;

        return $this;
    }

    /**
     * Get manual file.
     *
     * @return DocumentFile
     */
    public function getManualFile()
    {
        return $this->manualFile;
    }

    /**
     * Set addManualFile.
     *
     * @param bool $addManualFile
     *
     * @return Product
     */
    public function setAddManualFile($addManualFile)
    {
        $this->addManualFile = $addManualFile;

        return $this;
    }

    /**
     * Get addManualFile.
     *
     * @return bool
     */
    public function getAddManualFile()
    {
        return $this->addManualFile;
    }

    /**
     * Set packed by.
     *
     * @param string $packedBy
     *
     * @return Product
     */
    public function setPackedBy($packedBy)
    {
        $this->packedBy = $packedBy;

        return $this;
    }

    /**
     * Get packed by.
     *
     * @return int
     */
    public function getPackedBy()
    {
        return $this->packedBy;
    }

    /**
     * Set packaging unit.
     *
     * @param string $packagingUnit
     *
     * @return Product
     */
    public function setPackagingUnit($packagingUnit)
    {
        $this->packagingUnit = $packagingUnit;

        return $this;
    }

    /**
     * Get packaging unit.
     *
     * @return string
     */
    public function getPackagingUnit()
    {
        return $this->packagingUnit;
    }

    /**
     * Set storageUnit.
     *
     * @param string $storageUnit
     *
     * @return Product
     */
    public function setStorageUnit($storageUnit)
    {
        $this->storageUnit = $storageUnit;

        return $this;
    }

    /**
     * Get storageUnit.
     *
     * @return string
     */
    public function getStorageUnit()
    {
        return $this->storageUnit;
    }

    /**
     * Set stock.
     *
     * @param int $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock.
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set stockWarningAlert.
     *
     * @param int $stockWarningAlert
     *
     * @return Product
     */
    public function setStockWarningAlert($stockWarningAlert)
    {
        $this->stockWarningAlert = $stockWarningAlert;

        return $this;
    }

    /**
     * Get stockWarningAlert.
     *
     * @return int
     */
    public function getStockWarningAlert()
    {
        return $this->stockWarningAlert;
    }

    /**
     * Set stockDangerAlert.
     *
     * @param int $stockDangerAlert
     *
     * @return Product
     */
    public function setStockDangerAlert($stockDangerAlert)
    {
        $this->stockDangerAlert = $stockDangerAlert;

        return $this;
    }

    /**
     * Get stockDangerAlert.
     *
     * @return int
     */
    public function getStockDangerAlert()
    {
        return $this->stockDangerAlert;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return Product
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get movements.
     *
     * @return ArrayCollection
     */
    public function getMovements()
    {
        return $this->movements;
    }
}
