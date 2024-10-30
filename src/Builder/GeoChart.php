<?php

namespace Grafite\Charts\Builder;

use stdClass;
use Exception;

class GeoChart extends Chart
{
    public $projection = 'geoMercator';

    public $showGraticule = true;

    public $plugins = [
        '//cdn.jsdelivr.net/npm/chartjs-chart-geo@4.3.3/build/index.umd.min.js',
    ];

    public function options()
    {
        return [
            'showOutline' => true,
            'showGraticule' => $this->showGraticule,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'projection' => [
                    'axis' => 'x',
                    'projection' => $this->projection,
                ],
            ],
        ];
    }


    public $type = 'choropleth';

    public function parseGeoData($topojson)
    {
        if (is_scalar($topojson)) {
            $json = json_decode($topojson);
        } else {
            $json = $topojson;
        }

        if ($json->type != 'Topology') {
            throw new Exception('Type must be Topology');
        }

        if (property_exists($json, 'transform')) {
            $scale = $json->transform->scale;
            $translate = $json->transform->translate;
        } else {
            $scale = array(1, 1);
            $translate = array(0, 0);
        }

        $arcs = $json->arcs;
        $ret = array();

        foreach ($json->objects as $key => $object) {
            $ret[$key] = $this->topoObjectToGeoObject($object, $scale, $translate, $arcs);
        }

        return $ret;
    }

    protected function transformPoint($point, $scale, $translate)
    {
        $point[0] = $point[0] * $scale[0] + $translate[0];
        $point[1] = $point[1] * $scale[1] + $translate[1];
        return $point;
    }

    protected function decodeArc($arcs, $id, $scale, $translate)
    {
        $x = 0;
        $y = 0;

        if ($id >= 0) {
            $arc = $arcs[$id];
        } else {
            $arc = $arcs[0 - $id - 1];
        }

        $points = array();
        foreach ($arc as $point) {
            $point[0] = ($x += $point[0]) * $scale[0] + $translate[0];
            $point[1] = ($y += $point[1]) * $scale[1] + $translate[1];
            $points[] = $point;
        }
        if ($id < 0) {
            return array_reverse($points);
        }
        return $points;
    }

    protected function topoObjectToGeoObject($topo_obj, $scale, $translate, $arcs)
    {
        switch ($topo_obj->type) {
        case 'Point':
            $obj = new stdClass;
            $obj->type = 'Point';
            $obj->coordinates = $this->transformPoint($topo_obj->coordinates, $scale, $translate);
            break;

        case 'MultiPoint':
            $obj = new stdClass;
            $obj->type = $topo_obj->type;
            $obj->coordinates = array_map(function($point) use ($scale,$translate) {
                return $this->transformPoint($point, $scale, $translate);
            }, $topo_obj->coordinates);
            break;

        case 'Polygon':
            $linearrings = array();
            foreach ($topo_obj->arcs as $linestring_arc_ids) {
                $linestrings = array();
                foreach ($linestring_arc_ids as $arc_id) {
                    foreach ($this->decodeArc($arcs, $arc_id, $scale, $translate) as $point) {
                        if ($linestrings and $point == $linestrings[count($linestrings) - 1]) {
                            continue;
                        }
                        $linestrings[] = $point;
                    }
                }
                if (count($linestrings) < 4) {
                    continue;
                }
                $linearrings[] = $linestrings;
            }
            $obj = new stdClass;
            $obj->type = $topo_obj->type;
            $obj->coordinates = $linearrings;
            break;

        case 'GeometryCollection':
            $obj = new stdClass;
            $obj->type = 'FeatureCollection';
            $obj->features = array();
            foreach ($topo_obj->geometries as $geometry) {
                $geometry_obj = $this->topoObjectToGeoObject($geometry, $scale, $translate, $arcs);
                if ($geometry_obj->type != 'Feature') {
                    $geometry_feature_obj = new stdClass;
                    $geometry_feature_obj->type = 'Feature';
                    $geometry_feature_obj->properties = new stdClass;
                    $geometry_feature_obj->geometry = $geometry_obj;
                    $geometry_obj = $geometry_feature_obj;
                }
                $obj->features[] = $geometry_obj;
            }
            break;

        case 'MultiPolygon':
            $polygons = array();
            foreach ($topo_obj->arcs as $polygon_arc_id) {
                $linearrings = array();
                foreach ($polygon_arc_id as $i => $linestring_arc_ids) {
                    $linestrings = array();
                    foreach ($linestring_arc_ids as $arc_id) {
                        foreach ($this->decodeArc($arcs, $arc_id, $scale, $translate) as $point) {
                            if ($linestrings and $point == $linestrings[count($linestrings) - 1]) {
                                continue;
                            }
                            $linestrings[] = $point;
                        }
                    }
                    // linestrings muse have at least 4 point
                    if (count($linestrings) < 4) {
                        continue;
                    }
                    $linearrings[$i] = $linestrings;
                }
                if (count($linearrings) == 0) {
                    continue;
                }
                $polygons[] = $linearrings;
            }
            $obj = new stdClass;
            $obj->type = $topo_obj->type;
            $obj->coordinates = $polygons;
            break;

        case 'LineString':
            $linestring = array();
            foreach ($topo_obj->arcs as $arc_id) {
                foreach ($this->decodeArc($arcs, $arc_id, $scale, $translate) as $point) {
                    if ($linestring and $point == $linestring[count($linestring) - 1]) {
                        continue;
                    }
                    $linestring[] = $point;
                }
            }
            $obj = new stdClass;
            $obj->type = $topo_obj->type;
            $obj->coordinates = $linestring;
            break;

        case 'MultiLineString':
            $linestrings = array();
            foreach ($topo_obj->arcs as $linestring_arc_ids) {
                $linestring = array();
                foreach ($linestring_arc_ids as $arc_id) {
                    foreach ($this->decodeArc($arcs, $arc_id, $scale, $translate) as $point) {
                        if ($linestring and $point == $linestring[count($linestring) - 1]) {
                            continue;
                        }
                        $linestring[] = $point;
                    }
                }
                $linestrins[] = $linestring;
            }
            $obj = new stdClass;
            $obj->type = $topo_obj->type;
            $obj->coordinates = $linestrings;
            break;

        default:
            throw new Exception("Unsupported Topology type {$object->type}");
        }

        if (!property_exists($topo_obj, 'properties')) {
            return $obj;
        }

        $feature_obj = new stdClass;
        $feature_obj->type = 'Feature';
        $feature_obj->properties = $topo_obj->properties;
        $feature_obj->geometry = $obj;

        return $feature_obj;
    }
}
