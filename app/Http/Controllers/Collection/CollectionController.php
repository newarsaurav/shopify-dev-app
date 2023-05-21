<?php

namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shopify\Auth\OAuth;
use Shopify\Auth\OAuthCookie;

class CollectionController extends Controller
{
    public function getCollections()
    {
        $smart_colect_res = shopify_api_call('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', 'saruav-dev-app', '/admin/api/2022-04/smart_collections.json');
        $custom_colect_res = shopify_api_call('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', 'saruav-dev-app', '/admin/api/2022-04/custom_collections.json');
        $smart_collections = json_decode($smart_colect_res['response']);
        $custom_collections = json_decode($custom_colect_res['response']);

        array_map(
            function ($collection) {
                return $collection->type = 'custom_collection';
            },
            $custom_collections->custom_collections
        );

        array_map(
            function ($collection) {
                return $collection->type = 'smart_collection';
            },
            $smart_collections->smart_collections
        );


        $collections = array_merge($smart_collections->smart_collections, $custom_collections->custom_collections);
        return view('Collection.index', compact('collections'));
    }

    public function saveCollectionLocal()
    {
        $smart_colect_res = shopify_api_call('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', 'saruav-dev-app', '/admin/api/2022-04/smart_collections.json');
        $custom_colect_res = shopify_api_call('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', 'saruav-dev-app', '/admin/api/2022-04/custom_collections.json');
        $smart_collections = json_decode($smart_colect_res['response']);
        $custom_collections = json_decode($custom_colect_res['response']);

        array_map(
            function ($collection) {
                return $collection->type = 'custom_collection';
            },
            $custom_collections->custom_collections
        );

        array_map(
            function ($collection) {
                return $collection->type = 'smart_collection';
            },
            $smart_collections->smart_collections
        );


        $collections = array_merge($smart_collections->smart_collections, $custom_collections->custom_collections);

        foreach($collections as $collection){
            $collectionVal = new Collection();
            $collectionVal->type = $collection->title;
            $collectionVal->collection_id = $collection->id;
            $collectionVal->save();

        }
        return view('Collection.index', compact('collections'));
    }

    public function getSpecificCollection($type, $collection_id)
    {
        if ($type == 'smart_collection') {
            $res = shopify_api_call(
                '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',
                'saruav-dev-app',
                '/admin/api/2022-04/collections/' . $collection_id . '/products.json'
            );
        } else {
            $res = shopify_api_call(
                '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',
                'saruav-dev-app',
                '/admin/api/2022-04/collections/' . $collection_id . '/products.json'
            );
        }
        $products = (json_decode($res['response']));
        return view('Collection.detail.index', compact('products'));
    }
}
