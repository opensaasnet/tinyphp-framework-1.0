<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name IniParser.php
 * @author King
 * @version Beta 1.0
 * @Date 2020年2月27日下午5:37:29
 * @Description
 * @Class List
 *        1.IniParser Ini配置解析器类
 * @History King 2020年2月27日下午5:37:29 第一次建立该文件
 *          King 2020年2月27日下午5:37:29 修改
 *
 */
namespace Tiny\Config\Parser;

/**
 * ini配置文件解析器
 *
 * @package Tiny.Config.Parser
 * @since 2020年2月27日下午5:38:36
 * @final 2020年2月27日下午5:38:36
 */
class IniParser implements IParser
{

    /**
     * 解析INI配置文件，解析异常会抛出异常并终止
     *
     * {@inheritdoc}
     * @see \Tiny\Config\Parser\IParser::parse()
     */
    public function parse($fpath)
    {
        $data = NULL;
        try
        {
            $data = parse_ini_file($fpath, TRUE);
        }
        catch (\Exception $e)
        {
            throw new ParserException(sprintf('Failed to parse %s', $fpath), E_ERROR);
        }
        $this->_parseNode($data);
        return $data;
    }

    /**
     * 处理键名里包含.分隔符的情况
     *
     * @param array $data
     * @return void
     */
    protected function _parseNode(& $data)
    {
        foreach ($data as $node => & $d)
        {
            if (is_array($d))
            {
                $this->_parseNode($d);
            }

            if (strpos($node, '.') === FALSE)
            {
                continue;
            }

            // 处理键名里包含.分隔符的情况
            $ns = explode('.', $node);
            $adata = $d;
            unset($data[$node]);
            for ($i = count($ns) - 1; $i > 0; $i--)
            {
                $adata = [
                    $ns[$i] => $adata
                ];
            }
            if (is_array($data[$ns[0]]) && is_array($adata))
            {
                $data[$ns[0]] = array_merge_recursive($data[$ns[0]], $adata);
                continue;
            }
            $data[$ns[0]] = $adata;
        }

        return $data;
    }
}
?>