<?php

class TestSection extends OTable {

    public $counter = 0;
    public $TestSectionType_id = 0;
    public $Test_id = 0;
    public $parent_counter = 0;
    public static $mysql_table_name = "TestSection";

    public function mysql_delete() {
        $this->delete_object_links(TestSectionValue::get_mysql_table());
        parent::mysql_delete();
    }

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public function get_TestSectionType() {
        return DS_TestSectionType::from_mysql_id($this->TestSectionType_id);
    }

    public function get_parent_TestSection() {
        return TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $this->parent_counter), false);
    }

    public function get_values() {
        $result = array();
        $vals = TestSectionValue::from_property(array("TestSection_id" => $this->id));
        foreach ($vals as $v) {
            $result[$v->index] = $v->value;
        }
        return $result;
    }

    public function get_RFunctionName() {
        return "Test" . $this->Test_id . "Section" . $this->counter;
    }

    public function get_RFunction() {
        $code = "";

        if ($this->parent_counter != 0) {
            $next = $this->get_next_TestSection();
            $next_counter = ($next != null ? $next->counter : 0);

            $parent = TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $this->parent_counter), false);

            $parent_vals = $parent->get_values();

            $additional_conds = "";
            $i = 3;
            while (isset($parent_vals[$i])) {
                $additional_conds.=sprintf("%s %s %s %s", $parent_vals[$i], $parent_vals[$i + 1], $parent_vals[$i + 2], $parent_vals[$i + 3]);
                $i+=4;
            }

            $code = sprintf("
                if(%s %s %s %s) {
                    %s
                    }
                    else {
                    return(%d)
                    }
                    ", $parent_vals[0], $parent_vals[1], $parent_vals[2], $additional_conds, $this->get_RCode(), $next_counter);
        }
        else
            $code = $this->get_RCode();

        if (substr($this->get_RCode(), 0, 5) == "stop(")
            return sprintf("print('Start of section with index: <b>%s</b>')
                    %s", $this->counter, $this->get_RCode());

        return sprintf("
            %s <- function(){
            print('Start of section with index: <b>%s</b>')
                            %s
                 }
                 ", $this->get_RFunctionName(), $this->counter, $code);
    }

    public function get_RCode() {
        $code = "";

        $next = $this->get_next_TestSection();
        $next_counter = ($next != null ? $next->counter : 0);

        $vals = $this->get_values();
        switch ($this->TestSectionType_id) {
            case DS_TestSectionType::START: {
                    $code = sprintf("
                    CURRENT_SECTION_INDEX <<- %d  
                    
                    return(%d)
                    ", $next_counter, $next_counter);
                    return $code;
                }
            case DS_TestSectionType::END: {
                    $code = sprintf("
                    CURRENT_SECTION_INDEX <<- %d
                    set.var('CURRENT_SECTION_INDEX',%d)
                    return(%d)
                    ", $next_counter, $next_counter, $next_counter);
                    return $code;
                }
            case DS_TestSectionType::CUSTOM: {
                    $cs = CustomSection::from_mysql_id($vals[0]);
                    $parameters = $cs->get_parameter_CustomSectionVariables();
                    $returns = $cs->get_return_CustomSectionVariables();
                    $code = sprintf("
                        CURRENT_SECTION_INDEX <<- %d
                        ", $next_counter);
                    $j = 1;
                    foreach ($parameters as $param) {
                        $code.=sprintf("
                            %s <- %s
                            ", $param->name, $vals[$j]);
                        $j++;
                    }
                    $code.=$cs->code;
                    foreach ($returns as $ret) {
                        if ($vals[$j + 1] == 0 || $vals[$j + 1] == 2) {
                            $code.=sprintf("
                            %s <<- %s
                            ", $vals[$j], $ret->name);

                            if ($vals[$j + 2] == 1) {
                                $code.=sprintf("
                                %s <<- toString(%s)
                                ", $vals[$j], $vals[$j]);
                            }
                            if ($vals[$j + 2] == 2) {
                                $code.=sprintf("
                                %s <<- as.numeric(%s)
                                ", $vals[$j], $vals[$j]);
                            }
                        }

                        if ($vals[$j + 1] == 1 || $vals[$j + 1] == 2) {
                            $code.=sprintf("
                            set.var('%s',toString(%s))
                            ", $vals[$j], $ret->name);
                        }
                        $j = $j + 3;
                    }
                    $code.=sprintf("
                        return(%d)
                        ", $next_counter);
                    return $code;
                }
            case DS_TestSectionType::R_CODE: {
                    $code = sprintf("
                        CURRENT_SECTION_INDEX <<- %d
                        %s
                        return(%d)
                        ", $next_counter, $vals[0], $next_counter
                    );
                    return $code;
                }
            case DS_TestSectionType::LOAD_HTML_TEMPLATE: {
                    $template_id = $vals[0];
                    if ($template_id == 0)
                        $template_id = "CURRENT_TEMPLATE_ID";
                    else {
                        $template = Template::from_mysql_id($template_id);
                        if ($template == null)
                            return sprintf("stop('Invalid template id: %s in section #%s')", $template_id, $this->counter);
                    }

                    $code = sprintf("
                        CURRENT_SECTION_INDEX <<- %d
                        set.var('LOAD_HTML_SECTION_INDEX',%d)
                        set.var('CURRENT_SECTION_INDEX',%d)
                        
                        CURRENT_TEMPLATE_ID <<- %s
                        set.var('CURRENT_TEMPLATE_ID',CURRENT_TEMPLATE_ID)
                        set.var('HALT_TYPE',%d)
                        return(-1)
                        ", $next_counter, $this->counter, $next_counter, $template_id, $this->TestSectionType_id
                    );
                    return $code;
                }
            case DS_TestSectionType::GO_TO: {
                    $code = sprintf("
                        CURRENT_SECTION_INDEX <<- %d
                        set.var('CURRENT_SECTION_INDEX',%d)
                        return(%d)
                        ", $vals[0], $vals[0], $vals[0]
                    );
                    return $code;
                }
            case DS_TestSectionType::IF_STATEMENT: {
                    $code = sprintf("
                        CURRENT_SECTION_INDEX <<- %d
                        return(%d)
                        ", $next_counter, $next_counter);
                    return $code;
                }
            case DS_TestSectionType::TABLE_MOD: {
                    $type = $vals[0];
                    $set_count = $vals[2];
                    $where_count = $vals[1];

                    $table = Table::from_mysql_id($vals[3]);
                    if ($table == null)
                        return sprintf("stop('Invalid table id: %s in section #%s')", $vals[3], $this->counter);

                    $set = "";
                    for ($i = 0; $i < $vals[2]; $i++) {
                        $column = TableColumn::from_property(array("Table_id" => $vals[3], "index" => $vals[4 + $i * 2]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[4 + $i * 2], $vals[3], $this->counter);
                        if ($i > 0)
                            $set.=",";
                        $set.=sprintf("`%s`='\",toString(%s),\"'", $column->name, $vals[4 + $i * 2 + 1]);
                    }

                    $where = "";
                    for ($i = 0; $i < $vals[1]; $i++) {
                        $j = 4 + $vals[2] * 2 + $i * 4;
                        $column = TableColumn::from_property(array("Table_id" => $vals[3], "index" => $vals[$j + 1]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[$j + 1], $vals[3], $this->counter);

                        if ($i > 0)
                            $where .=sprintf("%s", $vals[$j]);
                        $where.=sprintf("`%s` %s '\",toString(%s),\"'", $column->name, $vals[$j + 2], $vals[$j + 3]);
                    }

                    $sql = "";
                    if ($type == 0) {
                        $sql.=sprintf("INSERT INTO `%s` SET %s", $table->get_table_name(), $set);
                    }
                    if ($type == 1) {
                        $sql.=sprintf("UPDATE `%s` SET %s WHERE %s", $table->get_table_name(), $set, $where);
                    }
                    if ($type == 2) {
                        $sql.=sprintf("DELETE FROM `%s` WHERE %s", $table->get_table_name(), $where);
                    }

                    $code = sprintf('
                        CURRENT_SECTION_INDEX <<- %d
                        
                        sqlCommand <- paste("%s",sep="")
                        sqlResult <- dbSendQuery(con,sqlCommand)
                        return(%d)
                        ', $next_counter, $sql, $next_counter);

                    return $code;
                }
            case DS_TestSectionType::SET_VARIABLE: {
                    $type = $vals[2];
                    $columns_count = $vals[0];
                    $conds_count = $vals[1];

                    $set_var_code = "";
                    if ($vals[4] == 1 || $vals[4] == 2)
                        $set_var_code = sprintf('set.var("%s",toString(%s))', $vals[6], $vals[6]);

                    $set_rvar_code = "";
                    if ($vals[4] == 0 || $vals[4] == 2)
                        $set_rvar_code = sprintf('
                                %s <<- %s
                                ', $vals[6], $vals[6]);
                    if ($vals[5] == 1) {
                        $set_rvar_code .= sprintf('
                                %s <<- toString(%s)
                                ', $vals[6], $vals[6]);
                    }
                    if ($vals[5] == 2) {
                        $set_rvar_code .= sprintf('
                                %s <<- as.numeric(%s)
                                ', $vals[6], $vals[6]);
                    }

                    if ($type == 0) {
                        $table = Table::from_mysql_id($vals[7]);
                        if ($table == null)
                            return sprintf("stop('Invalid table id: %s in section #%s')", $vals[7], $this->counter);

                        $column = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[8]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[8], $table->id, $this->counter);

                        $sql = sprintf("SELECT `%s`", $column->name);
                        for ($i = 1; $i <= $columns_count; $i++) {
                            $column = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[8 + $i]), false);
                            if ($column == null)
                                return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[8 + $i], $table->id, $this->counter);

                            $sql.=sprintf(",`%s`", $column->name);
                        }
                        $sql.=sprintf(" FROM `%s` ", $table->get_table_name());

                        if ($conds_count > 0) {
                            $sql.=sprintf("WHERE ");

                            $j = 9 + $columns_count;
                            for ($i = 1; $i <= $conds_count; $i++) {
                                if ($i > 1) {
                                    $link = $vals[$j];
                                    $j++;
                                }
                                else
                                    $j++;
                                $cond_col = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[$j]), false);
                                if ($cond_col == null)
                                    return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[$j], $table->id, $this->counter);

                                $j++;
                                $operator = $vals[$j];
                                $j++;
                                $exp = $vals[$j];
                                $j++;

                                if ($i > 1)
                                    $sql.=sprintf("%s `%s` %s '\",toString(%s),\"' ", $link, $cond_col->name, $operator, $exp);
                                else
                                    $sql.=sprintf("`%s` %s '\",toString(%s),\"' ", $cond_col->name, $operator, $exp);
                            }
                        }

                        $code = sprintf('
                        CURRENT_SECTION_INDEX <<- %d
                        
                        sqlCommand <- paste("%s",sep="")
                        sqlResult <- dbSendQuery(con,sqlCommand)
                        %s <- fetch(sqlResult,n=-1)
                        %s
                        %s
                        return(%d)
                        ', $next_counter, $sql, $vals[6], $set_var_code, $set_rvar_code, $next_counter);
                        return $code;
                    }
                    if ($type == 1) {
                        $code = sprintf('
                        CURRENT_SECTION_INDEX <<- %d
                        
                        %s <- {
                        %s
                        }
                        %s
                        %s
                        return(%d)
                        ', $next_counter, $vals[6], $vals[3], $set_var_code, $set_rvar_code, $next_counter
                        );
                        return $code;
                    }
                }
        }
        return $code;
    }

    public function get_next_TestSection() {
        $sql = sprintf("SELECT * FROM `%s` WHERE `Test_id`=%d AND `id`>%d LIMIT 0,1", TestSection::get_mysql_table(), $this->Test_id, $this->id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return TestSection::from_mysql_result($r);
        }
        return null;
    }

    public function to_XML() {
        $xml = new DOMDocument();

        $element = $xml->createElement("TestSection");
        $xml->appendChild($element);

        $counter = $xml->createElement("counter", htmlspecialchars($this->counter, ENT_QUOTES));
        $element->appendChild($counter);

        $parent = $xml->createElement("parent_counter", htmlspecialchars($this->parent_counter, ENT_QUOTES));
        $element->appendChild($parent);

        $tstid = $xml->createElement("TestSectionType_id", htmlspecialchars($this->TestSectionType_id, ENT_QUOTES));
        $element->appendChild($tstid);

        $tsv = $xml->createElement("TestSectionValues");
        $element->appendChild($tsv);

        $sv = TestSectionValue::from_property(array("TestSection_id" => $this->id));
        foreach ($sv as $v) {
            $elem = $v->to_XML();
            $elem = $xml->importNode($elem, true);

            $tsv->appendChild($elem);
        }

        return $element;
    }

}

?>