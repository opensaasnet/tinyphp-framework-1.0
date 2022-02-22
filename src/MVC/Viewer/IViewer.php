<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name IViewer.php
 * @author King
 * @version Beta 1.0
 * @Date 2017年3月13日上午12:17:34
 * @Class List
 * @Function List
 * @History King 2017年3月13日上午12:17:34 0 第一次建立该文件
 *          King 2017年3月13日上午12:17:34 1 上午修改
 *          King 2020年6月1日14:21 stable 1.0.01 审定
 */
namespace Tiny\MVC\Viewer;

/**
 * 视图模板引擎接口
 *
 * @package Tiny.Application.Viewer
 * @since : Mon Dec 12 01:06 15 CST 2011
 * @final : Mon Dec 12 01:06 15 CST 2011
 */
interface IViewer
{

    /**
     * 设置模板变量
     *
     * @param string $key
     *        键名 为Array时可设置多个参数名
     * @param mixed $value
     *        值
     * @return bool
     */
    public function assign($key, $value = NULL);

    /**
     * 输出模板解析后的数据
     *
     * @param string $file
     *        文件路径
     * @param bool $isAbsolute
     *        是否为绝对路径
     * @return string
     */
    public function fetch($file, $isAbsolute = FALSE);

    /**
     * 显示解析后的视图内容
     *
     * @param string $file
     *        视图文件路径
     * @param string $isAbsolute
     *        是否为绝对路径
     * @return string
     */
    public function display($file, $isAbsolute = FALSE);
}
?>