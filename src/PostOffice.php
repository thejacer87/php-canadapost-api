<?php

namespace CanadaPost;

/**
 * Canada Post API calls to get Post Office details.
 *
 * @package CanadaPost
 * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/findpostoffice/default.jsf
 */
class PostOffice extends ClientBase
{
    /**
     * Get information on Post Offices nearest to a given postal code.
     *
     * @param string $postal_code
     *   The postal code.
     * @param bool $d2po
     *   True indicates that you want a list of Post Offices that accept the
     *   "Deliver to Post Office" delivery of parcels. Use this when you want
     *   the parcel to be delivered directly to a Post Office rather than to the
     *   recipient’s address.
     * @param int $max_addresses
     *   The maximum number of Post Offices to return with the response. The
     *   maximum allowed is 50. In remote locations, fewer Post Offices than
     *   requested may be returned.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNearestPostOffice(
        $postal_code,
        $d2po = false,
        $max_addresses = 10,
        array $options = []
    ) {
        $query_params = "d2po={$d2po}&postalCode={$postal_code}&maximum={$max_addresses}";

        $response = $this->get(
            "rs/postoffice?{$query_params}",
            ['Accept' => 'application/vnd.cpc.postoffice+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get information on Post Offices nearest to a given location.
     *
     * @param string $province
     *   The province.
     * @param string $city
     *   The city.
     * @param string $street_name
     *   Street name onl (ie. without the house or apartment number). Street
     *   name can be a multi-part name with embedded spaces. If City and
     *   Province are specified, provision of this parameter will refine the
     *   list to a more specific location in the city indicated. For larger
     *   municipalities a list might not be returned if the street name is not
     *   provided in addition to the City and Province attributes.
     * @param bool $d2po
     *   True indicates that you want a list of Post Offices that accept the
     *   "Deliver to Post Office" delivery of parcels. Use this when you want
     *   the parcel to be delivered directly to a Post Office rather than to
     *   the recipient’s address.
     * @param int $max_addresses
     *   The maximum number of Post Offices to return with the response. The
     *   maximum allowed is 50. In remote locations, fewer Post Offices than
     *   requested may be returned.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNearestPostOfficeByAddress(
        $province = '',
        $city = '',
        $street_name = '',
        $d2po = false,
        $max_addresses = 10,
        array $options = []
    ) {
        $query_params = "d2po={$d2po}&province={$province}&city={$city}&streetName={$street_name}&maximum={$max_addresses}";

        $response = $this->get(
            "rs/postoffice?{$query_params}",
            ['Accept' => 'application/vnd.cpc.postoffice+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get information on Post Offices nearest to a given geographical
     * location.
     *
     * @param string $longitude
     *   The longitude. Format is 10 characters of the form: [-]3.5
     *   ie. up to 3 digits before the decimal and 5 digits after the decimal
     *   eg. -101.32354
     * @param string $latitude
     *   The latitude.
     *   Format is 10 characters of the form: [-]3.5
     *   ie. up to 3 digits before the decimal and 5 digits after the decimal
     *   eg. 55.32354
     * @param bool $d2po
     *   True indicates that you want a list of Post Offices that accept the
     *   "Deliver to Post Office" delivery of parcels. Use this when you want
     *   the parcel to be delivered directly to a Post Office rather than to
     *   the recipient’s address.
     * @param int $max_addresses
     *   The maximum number of Post Offices to return with the response. The
     *   maximum allowed is 50. In remote locations, fewer Post Offices than
     *   requested may be returned.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNearestPostOfficeByGeographicalLocation(
        $longitude,
        $latitude,
        $d2po = false,
        $max_addresses = 10,
        array $options = []
    ) {
        $query_params = "d2po={$d2po}&longitude={$longitude}&latitude={$latitude}&maximum={$max_addresses}";

        $response = $this->get(
            "rs/postoffice?{$query_params}",
            ['Accept' => 'application/vnd.cpc.postoffice+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get additional information about a specific Post Office.
     *
     * @param string $endpoint
     *   Link provided from Get Nearest Post Office.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPostOfficeDetail($endpoint, array $options = [])
    {
        $response = $this->get(
            $endpoint,
            ['Accept' => 'application/vnd.cpc.postoffice+xml'],
            $options
        );
        return $response;
    }
}
