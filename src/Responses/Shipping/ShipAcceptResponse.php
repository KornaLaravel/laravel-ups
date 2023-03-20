<?php

declare(strict_types=1);

namespace Rawilk\Ups\Responses\Shipping;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Rawilk\Ups\Entity\Entity;
use Rawilk\Ups\Entity\Payment\Charge;
use Rawilk\Ups\Entity\Shipment\BillingWeight;
use Rawilk\Ups\Entity\Shipment\PackageResult;

/**
 * @property \Illuminate\Support\Collection|\Rawilk\Ups\Entity\Payment\Charge[] $shipment_charges
 * @property string $shipment_identification_number
 *      Returned UPS shipment ID number; 1Z number of the first package in the shipment.
 * @property \Illuminate\Support\Collection|\Rawilk\Ups\Entity\Shipment\PackageResult[] $packages
 * @property \Rawilk\Ups\Entity\Shipment\BillingWeight $billing_weight
 */
class ShipAcceptResponse extends Entity
{
    public function setShipmentResultsAttribute(array $shipmentResults): void
    {
        $keysToPopulate = [
            'shipment_charges',
            'shipment_identification_number',
            'package_results',
            'billing_weight',
        ];

        foreach ($keysToPopulate as $key) {
            $methodName = 'set'.Str::of($key)->studly()->ucfirst();

            if (array_key_exists($key, $shipmentResults) && method_exists($this, $methodName)) {
                $this->$methodName($shipmentResults[$key]);
            }
        }
    }

    protected function setBillingWeight($billingWeight): void
    {
        if (is_array($billingWeight)) {
            $billingWeight = new BillingWeight($billingWeight);
        }

        $this->setAttribute('billing_weight', $billingWeight);
    }

    protected function setPackageResults(array $packageResults): void
    {
        // We have a single package returned if the array is keyed.
        if ($this->isAssociativeArray($packageResults)) {
            $packageResults = [$packageResults];
        }

        $this->attributes['packages'] = collect($packageResults)
            ->map(static function (array $data) {
                $instance = new PackageResult;

                return $instance->fill($instance->convertPropertyNamesToSnakeCase($data));
            });
    }

    protected function setShipmentCharges(array $charges): void
    {
        $this->attributes['shipment_charges'] = collect($charges)
            ->filter(fn ($charge) => is_array($charge))
            ->map(static function (array $charge, string $key) {
                return new Charge(array_merge($charge, [
                    'description' => (string) Str::of($key)->upper()->replace('_', ' '),
                ]));
            })
            ->filter(fn (Charge $charge) => $charge->monetary_value > 0)
            ->values();
    }

    protected function setShipmentIdentificationNumber(string $id): void
    {
        $this->attributes['shipment_identification_number'] = $id;
    }

    public function getPackagesAttribute($packages): Collection
    {
        return $packages ?? collect();
    }

    public function getShipmentChargesAttribute($charges): Collection
    {
        return $charges ?? collect();
    }

    public function billingWeight(): string
    {
        return BillingWeight::class;
    }
}
