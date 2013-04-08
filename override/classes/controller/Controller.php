<?php
abstract class Controller extends ControllerCore
{
	public function setTemplate($template)
	{
        if (Module::isInstalled('germanext'))
        {
            if ( ! class_exists('germanext', false))
            {
                require_once(_PS_MODULE_DIR_ . 'germanext/germanext.php');
            }
            
            $gn_template = Germanext::getTemplateByController(get_class($this), $template);
        }
        
		$this->template = (isset($gn_template) && $gn_template) ? $gn_template : $template;
	}
	
	public static function getGermanextFilePath($path)
	{
		require_once(_PS_MODULE_DIR_ . 'germanext/defines.php');

		$gn_path = false;
		$gn_path_rel = false;
		
		if (strpos($path, _THEME_CSS_DIR_) !== false) {
			$gn_path = GN_THEME_PATH . 'css/' . ltrim(strrchr($path, _THEME_CSS_DIR_), '/');
			$gn_path_rel = GN_REL_THEME_PATH . 'css/' . ltrim(strrchr($path, _THEME_CSS_DIR_), '/');
		}
		elseif (strpos($path, _THEME_JS_DIR_) !== false) {
			$gn_path = GN_THEME_PATH . 'js/' . ltrim(strrchr($path, _THEME_JS_DIR_), '/');
			$gn_path_rel = GN_REL_THEME_PATH . 'js/' . ltrim(strrchr($path, _THEME_JS_DIR_), '/');
		} else {
			$gn_path = GN_PATH . ltrim(substr($path, strlen(__PS_BASE_URI__)), '/');
			$gn_path_rel = GN_REL_PATH . ltrim(substr($path, strlen(__PS_BASE_URI__)), '/');
		}

		return ($gn_path && file_exists($gn_path)) ? $gn_path_rel : $path;
	}
	
	public function addCSS($css_uri, $css_media_type = 'all')
	{
		$css_override = array();
		
		if (is_array($css_uri))
		{
			foreach ($css_uri as $file => $media)
			{
				if (is_string($file))
				{
					$file = self::getGermanextFilePath($file);
					
					$css_override[$file] = $media;
				}
			}
		}
		else if (is_string($css_uri) && strlen($css_uri) > 1)
		{
			$file = self::getGermanextFilePath($css_uri);
					
			$css_override[$file] = $css_media_type;
		}
		
		parent::addCSS($css_override, $css_media_type);
	}
	
	public function addJS($js_uri)
	{
		if (is_array($js_uri))
		{
			foreach ($js_uri as &$js_file)
			{
				$js_file = self::getGermanextFilePath($js_file);
			}
		}
		else
		{
			$js_uri = self::getGermanextFilePath($js_uri);
		}
		
		parent::addJS($js_uri);
	}
}