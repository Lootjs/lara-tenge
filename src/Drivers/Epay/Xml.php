<?php
namespace Loot\Tenge\Drivers\Epay;

/*
-----===++[Additional procedures by Pavel Nedelin (soius@soius.kz;tecc@mail.kz)		]++===-----
-----===++[03.10.2006 - 05.10.2006							]++===-----
-----===++[(p) SOIUS Ltd. 2006 (soius@soius.kz)						]++===-----

-----===++[Дополнительные процедуры Pavel Nedelin (soius@soius.kz;tecc@mail.kz)		]++===-----
-----===++[03.10.2006 - 05.10.2006							]++===-----
-----===++[(p) SOIUS Ltd. 2006 (soius@soius.kz)						]++===-----
*/

// -----===++[Additional procedures start/Дополнительные процедуры начало]++===-----
class xml {
    // -----===++[Parse XML to ARRAY]++===-----
    // methods:
    // parse($data) - return array in format listed below
    // variables:
    // $data - string: incoming XML
    //
    // Array format:
    // array index:"TAG_"+tagNAME = value: tagNAME
    // example:$array['TAG_BANK'] = "BANK"
    // array index:NAME+"_"+ATTRIBUTE_NAME = value: ATTRIBUTE_VALUE
    // example:$array['BANK_NAME'] = "Kazkommertsbank JSC"
    //
    // -----===++[Резка XML в массив]++===-----
    // методы:
    // parse($data) - возвращает массив в формате описанном ниже
    // переменные:
    // $data - строка: входящий XML
    //
    // Формат массива:
    // индекс в массиве:"TAG_"+имяТега = значение: имяТега
    // пример:$array['TAG_BANK'] = "BANK"
    // индекс в массиве:имяТега+"_"+имяАттрибута = значение: значениеАттрибута
    // пример:$array['BANK_NAME'] = "Kazkommertsbank JSC"

    public $parser;
    public $xarray = array();
    public $lasttag;

    public function construct()
    {   $this->parser = xml_parser_create();
        xml_set_object($this->parser,$this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
    }

    public function parse($data)
    {
        xml_parse($this->parser, $data);
        ksort($this->xarray,SORT_STRING);
        return $this->xarray;
    }

    public function tag_open($parser, $tag, $attributes)
    {
        $this->lasttag = $tag;
        $this->xarray['TAG_'.$tag] = $tag;
        if (is_array($attributes)){
            foreach ($attributes as $key => $value) {
                $this->xarray[$tag.'_'.$key] = $value;
            };
        };
    }

    public function cdata($parser, $cdata)
    {	$tag = $this->lasttag;
        $this->xarray[$tag.'_CHARDATA'] = $cdata;
    }

    public function tag_close($parser, $tag)
    {}
}
