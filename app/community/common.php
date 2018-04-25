<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 获取社区配置，通用
 * @param string $key 配置键值,都小写
 * @return array
 */
function cmf_get_community_option($key)
{
    if (!is_string($key) || empty($key)) {
        return [];
    }

    static $cmfGetOption;

    if (empty($cmfGetOption)) {
        $cmfGetOption = [];
    } else {
        if (!empty($cmfGetOption[$key])) {
            return $cmfGetOption[$key];
        }
    }

    $optionValue = cache('cmf_community_option_' . $key); // cmf_options_admin_dashboard_widgets

    if (empty($optionValue)) {
        $optionValue = \think\Db::name('community_option')->where('option_name', $key)->value('option_value');
        if (!empty($optionValue)) {
            $optionValue = json_decode($optionValue, true);

            cache('cmf_community_option_' . $key, $optionValue);
        }
    }

    $cmfGetOption[$key] = $optionValue;

    return $optionValue;
}

/**
 * 设置社区配置，通用
 * @param string $key 配置键值,都小写
 * @param array $data 配置值，数组
 * @param bool $replace 是否完全替换
 * @return bool 是否成功
 */
function cmf_set_community_option($key, $data, $replace = false)
{
    if (!is_array($data) || empty($data) || !is_string($key) || empty($key)) {
        return false;
    }

    $key        = strtolower($key);
    $option     = [];
    $findOption = \think\Db::name('community_option')->where('option_name', $key)->find();
    if ($findOption) {
        if (!$replace) {
            $oldOptionValue = json_decode($findOption['option_value'], true);
            if (!empty($oldOptionValue)) {
                $data = array_merge($oldOptionValue, $data);
            }
        }

        $option['option_value'] = json_encode($data);
        \think\Db::name('community_option')->where('option_name', $key)->update($option);
        \think\Db::name('community_option')->getLastSql();
    } else {
        $option['option_name']  = $key;
        $option['option_value'] = json_encode($data);
        \think\Db::name('community_option')->insert($option);
    }

    cache('cmf_community_option_' . $key, null);//删除缓存

    return true;
}