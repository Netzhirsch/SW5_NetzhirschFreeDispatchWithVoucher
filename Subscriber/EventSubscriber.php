<?php

namespace NetzhirschFreeDispatchWithVoucher\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Hook_HookArgs;

class EventSubscriber implements SubscriberInterface
{
	
	/**
	 * @var Connection
	 */
	private $connection;
	
	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}
	
	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
    {
        return [
	        'sAdmin::sGetDispatchBasket::after' => 'subtractedVouchersFromBasket'
        ];
    }

	/**
	 * @param Enlight_Hook_HookArgs $args
	 * @return mixed
	 */
    public function subtractedVouchersFromBasket(Enlight_Hook_HookArgs $args)
    {
	    $basket = $args->getReturn();
	    $vouchers = $this->getVouchers($basket['sessionID']);
	    foreach ($vouchers as $voucher) {
		    $basket['amount_display'] -= $voucher['price'];
	    }
		$basket['amount_display'] = round($basket['amount_display'], 2);
	    
	    return $basket;
    }
	
	/**
	 * @param $sessionID
	 * @return array Vouchers
	 */
	private function getVouchers($sessionID)
	{
		$query = $this->connection->createQueryBuilder();
		$query->select('basket.price')
			->from('s_order_basket', 'basket')
			->andWhere('basket.modus = 2')
			->andWhere('basket.sessionID = :sessionID')
			->setParameter('sessionID', $sessionID);
		
		return $query->execute()->fetchAll();
	}
}