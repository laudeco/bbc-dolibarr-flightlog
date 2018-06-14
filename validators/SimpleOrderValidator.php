<?php
require_once __DIR__ . '/AbstractValidator.php';
require_once __DIR__ . '/../class/bbcvols.class.php';


/**
 * Validate a simple order.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class SimpleOrderValidator extends AbstractValidator
{
    /**
     * @var Product
     */
    private $defaultService;

    /**
     * @inheritDoc
     */
    public function __construct(Translate $langs, DoliDB $db, $defaultServiceId)
    {
        parent::__construct($langs, $db);
        $this->fetchService($defaultServiceId);
    }

    /**
     * @param $defaultServiceId
     */
    private function fetchService($defaultServiceId)
    {
        $this->defaultService = new Product($this->db);
        $this->defaultService->fetch($defaultServiceId);
    }

    /**
     * Get a value as input and validates it. If an error occurs, it returns error messages.
     *
     * @param stdClass $value
     * @param array $context
     *
     * @return bool
     */
    public function isValid($value, $context = [])
    {

        $this->valid = true;

        if(empty($value->name)){
            $this->addError('name', 'Le nom est requis pour créer une commande');
        }
        if(empty($value->email) && empty($value->phone)){
            $this->addWarning('Soit l\'e-mail soit le téléphone n\'a pas été complété');
        }
        if(empty($value->nbrPax) || (int)$value->nbrPax <= 0){
            $this->addError('nbrPax', 'Le nombre de passagers doit être plus grand que 0.');
        }
        if(empty($value->region)){
            $this->addError('region', 'Le lieu de décollage doit etre configuré');
        }

        if((int)$value->nbrPax > 0){
            $costPerPax = $value->cost / (int)$value->nbrPax;
            if($costPerPax < $this->getMinPrice()){
                $this->addError('cost', sprintf('Le prix demandé par passagé est trop peu élevé. Un minimum de %s est demandé', $this->getMinPrice()));
            }
        }

        return $this->valid;
    }

    /**
     * Returns the minimum price.
     *
     * @return int
     */
    private function getMinPrice()
    {
        if ($this->defaultService->price_base_type == 'TTC') {
            return $this->defaultService->price_min;
        }
        return $this->defaultService->price_min_ttc;
    }
}