<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCodes extends Model
{
    use HasFactory;
    protected $fillable = [
        'Postcode',
        'InUse',
        'Latitude',
        'Longitude',
        'Easting',
        'Northing',
        'GridRef',
        'County',
        'District',
        'Ward',
        'DistrictCode',
        'WardCode',
        'Country',
        'CountyCode',
        'Constituency',
        'Introduced',
        'Terminated',
        'Parish',
        'NationalPark',
        'Population',
        'Households',
        'BuiltUpArea',
        'BuiltUpSubDivision',
        'LowerLayerSuperOutputArea',
        'RuralUrban',
        'Region',
        'Altitude',
        'LondonZone',
        'LSOACode',
        'LocalAuthority',
        'MSOACode',
        'MiddleLayerSuperOutputArea',
        'ParishCode',
        'CensusOutputArea',
        'ConstituencyCode',
        'IndexOfMultipleDeprivation',
        'Quality',
        'UserType',
        'LastUpdated',
        'NearestStation',
        'DistanceToStation',
        'PostcodeArea',
        'PostcodeDistrict',
        'PoliceForce',
        'WaterCompany',
        'PlusCode',
        'AverageIncome',
        'SewageCompany',
        'TravelToWorkArea'
    ];
}
