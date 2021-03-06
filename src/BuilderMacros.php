<?php

declare(strict_types=1);

/**
 * This file is part of Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\ScoutExtended;

use Closure;
use Laravel\Scout\Builder;
use function call_user_func;

/**
 * @internal
 */
final class BuilderMacros
{
    /**
     * @return \Closure
     */
    public function count(): Closure
    {
        /*
         * Count the number of items in the search results.
         *
         * @return int
         */
        return function (): int {
            $raw = $this->engine()->search($this);

            return array_key_exists('nbHits', $raw) ? (int) $raw['nbHits'] : 0;
        };
    }

    /**
     * @return \Closure
     */
    public function aroundLatLng(): Closure
    {
        /*
         * Search for entries around a given location.
         *
         * @link https://www.algolia.com/doc/guides/geo-search/geo-search-overview/
         *
         * @param float $lat Latitude of the center
         * @param float $lng Longitude of the center
         *
         * @return \Laravel\Scout\Builder
         */
        return function (float $lat, float $lng): Builder {
            $callback = $this->callback;
            $this->callback = function ($algolia, $query, $options) use ($lat, $lng, $callback) {
                $options['aroundLatLng'] = (float) $lat.','.(float) $lng;
                if ($callback) {
                    return call_user_func($callback, $algolia, $query, $options);
                }

                return $algolia->search($query, $options);
            };

            return $this;
        };
    }

    /**
     * @return \Closure
     */
    public function with(): Closure
    {
        /*
         * Customize the request with the provided options
         *
         * @param array $options Latitude of the center
         *
         * @return \Laravel\Scout\Builder
         */
        return function (array $options): Builder {
            $callback = $this->callback;

            $this->callback = function ($algolia, $query, $baseOptions) use ($options, $callback) {
                $options = array_merge($options, $baseOptions);

                if ($callback) {
                    return call_user_func($callback, $algolia, $query, $options);
                }

                return $algolia->search($query, $options);
            };

            return $this;
        };
    }
}
