<?php

abstract class Dao_Base
{

    public static $modelName;
    public static $primaryKey = 'id';
    public static $softDelete = false;
    public static $filepath = '';

    public static $relation = array();

    /**
     * @name Generic Select Method
     * @params $params
     * @return Array
     */
    public static function select(array $params = array(), $lang = null)
    {
        $defaults = array();

        $params = array_merge($defaults, $params);

        $objects = array();

        $filters = array(
            'where' => array(),
            'limit' => '',
            'order' => '',
        );

        $filters = static::setParameters($filters, $params);


        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "SELECT * FROM " . static::$modelName . (count($filters['where']) ? " WHERE " . implode(' AND ', $filters['where']) : '') . "{$filters['order']}{$filters['limit']};", __LINE__);
        $sql->execute(__LINE__);
        while ($r = $sql->fetchAssoc())
        {

            static::handleRelations($r);

            if (!empty($params['indexby']))
            {
                $objects[$r[$params['indexby']]] = $r;
            } else
            {
                $objects[] = $r;
            }
        }


        return $objects;
    }

    /**
     * @name Generic Select Method
     * @params $params
     * @return Array
     */
    public static function total(array $params = array(), $lang = null)
    {
        $defaults = array();

        $params = array_merge($defaults, $params);

        $objects = array();

        $filters = array(
            'where' => array(),
            'limit' => '',
            'order' => '',
        );

        $filters = static::setParameters($filters, $params);

        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "SELECT COUNT(*) as total FROM " . static::$modelName . (count($filters['where']) ? " WHERE " . implode(' AND ', $filters['where']) : '') . ";", __LINE__);
        $sql->execute(__LINE__);
        $object = $sql->fetchAssoc();

        return $object['total'];
    }

    protected static function setParameters($filters, $params)
    {
        return self::setBaseParameters($filters, $params);
    }

    protected static function setBaseParameters($filters, $params)
    {

        $params['dir'] = !empty($params['dir']) ? $params['dir'] : 'ASC';

        if (empty($filters['where']))
        {
            $filters['where'] = array();
        }

        # LIMIT
        if (!empty($params['limit']))
        {
            $filters['limit'] = ' LIMIT ' . $params['limit'];
        }

        # WHERE
        if (!empty($params['where']))
        {
            $filters['where'] = array_merge($filters['where'], $params['where']);
        }

        # PAGE
        if (!empty($params['page']) && !empty($params['limit']))
        {
            $filters['limit'] = ' LIMIT ' . (((int)$params['page'] - 1) * (int)$params['limit']) . ', ' . $params['limit'];
        }

        # OFFSET
        if (!empty($params['offset']) && !empty($params['limit']))
        {
            $filters['limit'] = ' LIMIT ' . $params['offset'] . ',' . $params['limit'];
        }

        # ORDER
        if (!empty($params['order']))
        {
            if (!is_array($params['order']))
            {
                $filters['order'] = ' ORDER BY ' . $params['order'] . ' ' . $params['dir'];
            } else
            {
                $filters['order'] = array();
                foreach ($params['order'] as $k => $order)
                {
                    $filters['order'][] = $order . ' ' . $params['dir'][$k];
                }

                $filters['order'] = ' ORDER BY ' . implode(', ', $filters['order']);
            }
        }

        # SOFTDELETE
        if (static::$softDelete && empty($params['with_deleted']))
        {
            $filters['where'][] = "deleted IS NULL";
        }

        return $filters;
    }

    /**
     * @name Generic Get Method
     * @params $id
     * @return Array
     */
    public static function get($id = null, $lang = null, $skip_relations = false)
    {

        if (empty($id))
        {
            return false;
        }

        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "SELECT * FROM " . static::$modelName . " WHERE " . static::$primaryKey . " = {$id} LIMIT 1;", __LINE__);
        $sql->execute(__LINE__);

        $row = $sql->fetchAssoc();

        if ($skip_relations == false)
        {
            static::handleRelations($row);
        }

        return $row;
    }

    /**
     * @name Generic magic get method
     * @params $id
     * @return Array
     */
    public static function __callStatic($name, $arguments)
    {

        switch (true)
        {
            case strpos($name, 'getBy') !== FALSE: # GetBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 5), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
                $sql->embed('query', "SELECT * FROM " . static::$modelName . " WHERE {$column} = '{$arguments[0]}' LIMIT 1;", __LINE__);
                $sql->execute(__LINE__);

                $row = $sql->fetchAssoc();

                if (empty($arguments[2]))
                {
                    static::handleRelations($row);
                }

                return $row;
                break;

            case strpos($name, 'selectBy') !== FALSE: # SelectBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 8), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                if (!isset($arguments[1]))
                {
                    $arguments[1] = array();
                }

                $defaults = array();

                $arguments[1] = array_merge($defaults, $arguments[1]);

                $objects = array();

                $filters = array(
                    'where' => array("{$column} = '{$arguments[0]}'"),
                    'limit' => '',
                    'order' => '',
                );

                $filters = static::setParameters($filters, $arguments[1]);

                $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
                $sql->embed('query', "SELECT * FROM " . static::$modelName . (count($filters['where']) ? " WHERE " . implode(' AND ', $filters['where']) : '') . "{$filters['order']}{$filters['limit']};", __LINE__);
                $sql->execute(__LINE__);
                while ($r = $sql->fetchAssoc())
                {

                    static::handleRelations($r);

                    if (!empty($arguments[1]['indexby']))
                    {
                        $objects[$r[$arguments[1]['indexby']]] = $r;
                    } else
                    {
                        $objects[] = $r;
                    }
                }

                return $objects;
                break;

            # DELETE BY COLUMN
            case strpos($name, 'deleteBy') !== FALSE: # deleteBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 8), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
                $sql->embed('query', "DELETE FROM " . static::$modelName . " WHERE {$column} = '{$arguments[0]}';", __LINE__);
                $sql->execute(__LINE__);

                return true;
                break;

            case strpos($name, 'selectTotalBy') !== FALSE: # SelectBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 13), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                if (!isset($arguments[1]))
                {
                    $arguments[1] = array();
                }

                $defaults = array();

                $arguments[1] = array_merge($defaults, $arguments[1]);

                $objects = array();

                $filters = array(
                    'where' => array("{$column} = '{$arguments[0]}'"),
                    'limit' => '',
                    'order' => '',
                );

                $filters = static::setParameters($filters, $arguments[1]);

                $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
                $sql->embed('query', "SELECT COUNT(*) as total FROM " . static::$modelName . (count($filters['where']) ? " WHERE " . implode(' AND ', $filters['where']) : '') . ";", __LINE__);
                $sql->execute(__LINE__);
                $object = $sql->fetchAssoc();

                return $object['total'];
                break;

            case strpos($name, 'selectInBy') !== FALSE: # SelectBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 10), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                if (!isset($arguments[1]))
                {
                    $arguments[1] = array();
                }

                $defaults = array();

                $arguments[1] = array_merge($defaults, $arguments[1]);

                $objects = array();

                if (empty($arguments[0]))
                {
                    return $objects;
                }

                $filters = array(
                    'where' => array("{$column} IN (" . implode(', ', $arguments[0]) . ")"),
                    'limit' => '',
                    'order' => '',
                );

                $filters = static::setParameters($filters, $arguments[1]);

                $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
                $sql->embed('query', "SELECT * FROM " . static::$modelName . (count($filters['where']) ? " WHERE " . implode(' AND ', $filters['where']) : '') . "{$filters['order']}{$filters['limit']};", __LINE__);
                $sql->execute(__LINE__);
                while ($r = $sql->fetchAssoc())
                {
                    if (!empty($arguments[1]['indexby']))
                    {
                        $objects[$r[$arguments[1]['indexby']]] = $r;
                    } else
                    {
                        $objects[] = $r;
                    }
                }

                return $objects;
                break;

            case strpos($name, 'selectInTotalBy') !== FALSE: # SelectBy param
                $matches = array();
                $column = preg_match_all('/[A-Z][a-z]+/', substr($name, 15), $matches);
                $column = strtolower(implode('_', $matches[0]));

                # deny when $column is empty
                if (empty($column))
                {
                    return false;
                }

                if (!isset($arguments[1]))
                {
                    $arguments[1] = array();
                }

                $defaults = array();

                $params = array_merge($defaults, $arguments[1]);

                $objects = Doctrine_Query::create()
                    ->select('count(o.id) AS total')
                    ->from(static::$modelName . ' o')
                    ->andWhereIn("o.$column", $arguments[0])
                    ->andWhere('o.deleted_at IS NULL');

                # PARAMETRYZACJA
                unset($params['order'], $params['page'], $params['limit'], $params['offset']);
                $objects = static::setParameters($objects, $params);

                if (isset($arguments[2]) && $arguments[2] !== null)
                {
                    $objects->leftJoin('o.Translation t')
                        ->andWhere('t.lang = ?', $arguments[2]);
                }
                return $objects->fetchOne()->total;
                break;
        }
    }

    /**
     * @name Generic Insert Method
     * @params $data, $user_id
     * @return Array
     */
    public static function insert(array $data, $user_id = null)
    {

        $output = array(
            'status' => 'OK',
            'message' => array(),
            'insert_id' => null,
        );

        # deny when user_id not specified
//		if ($user_id === null) {
//			return false;
//		}

        $output = static::validate($output, $data);

        # Zwracamy błędy w przypadku...błędów.
        if ($output['status'] != 'OK')
        {
            return $output;
        }

        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "INSERT INTO " . static::$modelName . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode('\', \'', $data) . "');", __LINE__);
        $sql->execute(__LINE__);

        $output['id'] = $sql->insertId();

        return $output;
    }

    /**
     * @name Generic Validator Method
     * @params $data
     * @return Array $data
     */
    public static function validate($output, &$data)
    {
        return $output;
    }

    /**
     * @name Generic Update Method
     * @params $data, $user_id
     * @return Array
     */
    public static function update(array $data, $user_id = null)
    {

        # deny when user_id not specified
//		if ($user_id === null || empty($data['id'])) {
//			return false;
//		}

        if (empty($data[static::$primaryKey]))
        {
            return false;
        }

        $id = $data[static::$primaryKey];
        unset($data[static::$primaryKey]);

        $set = array();
        foreach ($data as $k => $entry)
        {
            $set[] = "{$k} = '{$entry}'";
        }

        if (!empty($params['debug']))
        {
            echo "UPDATE " . static::$modelName . " SET " . implode(', ', $set) . " WHERE " . static::$primaryKey . " = '{$id}';";
            unset($params['debug']);
        }

        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "UPDATE " . static::$modelName . " SET " . implode(', ', $set) . " WHERE " . static::$primaryKey . " = '{$id}';", __LINE__);
        $sql->execute(__LINE__);

//		return $object;
        return array('status' => 'OK', 'id' => $id);
    }

    /**
     * @name Generic Delete Method
     * @params $data, $user_id
     * @return Array
     */
    public static function delete($id = null, $user_id = null)
    {

        # deny when id or user_id not specified
//		if ($user_id === null || $id === null) {
//			return false;
//		}

        # deny when id not specified
        if (empty($id))
        {
            return false;
        }

        if (static::$softDelete)
        {
            $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
            $sql->embed('query', "UPDATE " . static::$modelName . " SET deleted = CURRENT_TIMESTAMP WHERE id = {$id};", __LINE__);
            $sql->execute(__LINE__);
        } else
        {
            $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
            $sql->embed('query', "DELETE FROM " . static::$modelName . " WHERE id = {$id};", __LINE__);
            $sql->execute(__LINE__);
        }

        return true;
    }

    /**
     * @name Generic Restore Method
     * @params $data, $user_id
     * @return Array
     */
    public static function restore($id = null, $user_id = null)
    {

        # deny when id or user_id not specified
//		if ($user_id === null || $id === null) {
//			return false;
//		}

        # deny when id not specified
        if (empty($id))
        {
            return false;
        }

        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "UPDATE " . static::$modelName . " SET deleted = NULL WHERE id = {$id};", __LINE__);
        $sql->execute(__LINE__);

        return true;
    }

    /**
     * @name Generic Unique Method
     * @params $data, $user_id
     * @return Array
     */
    public static function exists($column, $value)
    {

        $object = Doctrine_Query::create()
            ->from(static::$modelName . ' o')
            ->andWhere("o.$column = ?", $value)
            ->fetchOne();

        return $object;
    }

    /**
     * @name Generic table display
     */
    public static function table(array $params = array(), $lang = null)
    {

        $objects = static::select($params, $lang);

        if (!count($objects))
        {
            return 'Brak danych';
        }

        $return = "<table border=\"1\" style=\"font-size: 11px; font-family: 'Courier new'; border-collapse: collapse;\" cellspacing=\"0\" cellpadding=\"1\">\n";

        $return .= "\t<tr>\n";
        foreach (array_keys($objects[0]) as $column)
        {
            $return .= "\t\t<th nowrap=\"nowrap\">{$column}</th>\n";
        }
        $return .= "\t</tr>\n";

        foreach ($objects as $row)
        {
            $return .= "\t\t<tr>";
            foreach ($row as $field)
            {
                if (is_null($field))
                {
                    $field = '<i></i>';
                }
                $return .= "\t\t\t<td>$field</td>\n";
            }
            $return .= "\t\t</tr>";
        }

        $return .= "</table>";

        return $return;
    }

    /**
     * @name Generic moderation display
     */
    public static function adminView($params = array(), $columns_translations = array(), $skip = array(), $custom_functions = array(), $custom_name = null, $filters = array())
    {

        # akcje domyślne
        if (!empty($_GET['delete']))
        {
            static::delete((int)$_GET['delete']);
        }
        if (!empty($_GET['restore']))
        {
            static::restore((int)$_GET['restore']);
        }

        if (static::$softDelete)
        {
            $params['with_deleted'] = true;
        }
        $lang = null;

        $filters_body = array();

        if (!empty($_GET['filters']))
        {
            foreach ($_GET['filters'] as $k => $filter)
            {
                # BUDOWA ZAPYTANIA
                if (!empty($filters[$k]))
                {
                    switch ($filters[$k]['type'])
                    {
                        case 'select':
                            $params['where'][] = $filters[$k]['column'] . ' = \'' . $filter . '\'';
                            break;
                    }
                }
            }
        }

        if (!empty($filters))
        {
            foreach ($filters as $k => $filter)
            {
                # BUDOWA FILTRU
                switch ($filter['type'])
                {
                    case 'select':
                        $filter_body = '<select name="filters[artist_id]">';
                        foreach ($filter['module']::select($filter['params']) as $i => $v)
                        {
                            $filter_body .= '<option value="' . $v[$filter['value']] . '"' . (!empty($_GET['filters']) && ($_GET['filters'][$k] == $v[$filter['value']]) ? ' selected="selected"' : '') . '>' . $v[$filter['name']] . '</option>';
                        }
                        $filter_body .= '</select>';
                        $filters_body[] = $filter_body;
                        break;
                }
            }
        }

        if (!empty($params['custom_filters']))
        {
            $filters_body = $params['custom_filters'];
            unset($params['custom_filters']);
        }

        $objects = static::select($params, $lang);

        if (!count($objects))
        {
            //return 'Brak danych';
            $objects = array();
        }

        $columns = static::getColumns();

        $title = !empty($custom_name) ? $custom_name : 'Administracja - ' . static::$modelName;

        $return = '<!--<h1>' . $title . '</h1>-->';

        $return .= "<button type=\"button\" name=\"add\" class=\"add\" value=\"forms/{$params['form']}\">Dodaj nowy</button>";

        $return .= implode($filters_body);

        $return .= "<div class=\"sTable\">\n\t<table class=\"sTableContent\" id=\"contentTable\" border=\"1\" style=\"font-size: 11px; font-family: 'Courier new'; border-collapse: collapse;\" cellspacing=\"1\" cellpadding=\"0\">\n";

        $return .= "\t<tr class=\"sTableContentHeader\">\n";
        $return .= "\t\t<th style=\"width: 17px;\"></th>\n";
        foreach ($columns as $column)
        {
            if (in_array($column, $skip))
            {
                continue;
            }

            $column = !empty($columns_translations[$column]) ? $columns_translations[$column] : $column;

            if (in_array($column, @array_keys($custom_functions)))
            {
                $column = !empty($custom_functions[$column]['name']) ? $custom_functions[$column]['name'] : $column;
            }
            $return .= "\t\t<th nowrap=\"nowrap\">{$column}</th>\n";
        }
        foreach ($custom_functions as $column => $function)
        {
            if (in_array($column, array_keys($columns)))
            {
                continue;
            }
            $return .= "\t\t<th nowrap=\"nowrap\">{$function['name']}</th>\n";
        }
        $return .= "\t\t<th class=\"actions\" nowrap=\"nowrap\">Akcje</th>\n";
        $return .= "\t</tr>\n";
        $i = 0;
        foreach ($objects as $row)
        {
            $i++;
            $return .= "\t<tr class=\"" . (($i % 2) ? 'sTableContentRowEven' : 'sTableContentRowOdd') . "\">\n";
            $return .= "\t<td><input type=\"checkbox\" name=\"form[id]\" value=\"{$row['id']}\" />";
            foreach ($row as $column => $field)
            {
                if (in_array($column, $skip))
                {
                    continue;
                }

                if (in_array($column, array_keys($custom_functions)))
                {
                    if (!empty($custom_functions[$column]['skip_tag']) && $custom_functions[$column]['skip_tag'] == true)
                    {
                        $return .= "\t\t" . $custom_functions[$column]['body']($row) . "\n";
                    } else
                    {
                        $return .= "\t\t<td nowrap=\"nowrap\">" . $custom_functions[$column]['body']($row) . "</td>\n";
                    }
                    continue;
                }

                if (is_null($field))
                {
                    $field = '<i></i>';
                }
                $return .= "\t\t<td nowrap=\"nowrap\">$field</td>\n";
            }

            foreach ($custom_functions as $column => $function)
            {
                if (in_array($column, array_keys($row)))
                {
                    continue;
                }
                if (!empty($function['skip_tag']) && $function['skip_tag'] == true)
                {
                    $return .= "\t\t" . $function['body']($row) . "\n";
                } else
                {
                    $return .= "\t\t<td nowrap=\"nowrap\">" . $function['body']($row) . "</td>\n";
                }
            }

            $edit = '<a href="' . "forms/{$params['form']}" . '?id=' . $row[static::$primaryKey] . '" class="edit">edytuj</a>';
            $delete = static::$softDelete && !is_null($row['deleted']) ? '<a href="?restore=' . $row[static::$primaryKey] . '">przywróć</a>' : '<a class="delete-entry" href="?delete=' . $row[static::$primaryKey] . '">usuń</a>';

            $return .= "\t\t<td class=\"actions\" nowrap=\"nowrap\">[{$edit}] [{$delete}]</td>\n";
            $return .= "\t</tr>\n";
        }

        $return .= "\n\t</table>\t</div>";

        return $return;
    }

    protected static function readonly($name, $value)
    {
        return '<input readonly="readonly type="text" id="' . str_replace('_', '-', static::$modelName) . '-' . $name . '" name="' . static::$modelName . '[' . $name . ']" value="' . $value . '" />';
    }

    protected static function input($name, $value)
    {
        return '<input type="text" id="' . str_replace('_', '-', static::$modelName) . '-' . $name . '" name="' . static::$modelName . '[' . $name . ']" value="' . $value . '" />';
    }

    protected static function textarea($name, $value)
    {
        return '<textarea id="' . str_replace('_', '-', static::$modelName) . '-' . $name . '" name="' . static::$modelName . '[' . $name . ']">' . $value . '</textarea>';
    }

    //protected static function

    public static function form($params = array(), $columns_translations = array(), $skip = array(), $custom_functions = array(), $readonly = array(), $title = null)
    {

        $skip[] = 'deleted';
        $skip[] = 'id';
        $skip[] = 'created';

        $lang = null;

        $javascript = array(
            '',
        );

        # Tytuł
        $title = !empty($title) ? $title : 'Administracja - ' . static::$modelName;

        $return = '<h1 class="col-sm-12">' . $title . '</h1>';

        # Columns
        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "SHOW COLUMNS FROM " . static::$modelName . ";", __LINE__);
        $sql->execute(__LINE__);

        if (!empty($params[static::$primaryKey]))
        {
            $values = static::get((int)$params[static::$primaryKey], $lang, true);
        }

        $columns = array();

        while ($r = $sql->fetchAssoc())
        {
            $k = $r['Field'];

            if (in_array($k, $skip))
            {
                continue;
            }

            # Type
            $matches = array();
            preg_match('/^([a-z0-9]+)(\()?([0-9]*)(\))?$/', $r['Type'], $matches);
            $type = $matches[1];
            $length = !empty($matches[3]) ? (int)$matches[3] : 0;
            $content = '';
            $value = isset($values[$k]) ? $values[$k] : (!is_null($r['Default']) ? str_replace('CURRENT_TIMESTAMP', date('Y-m-d H:i:s'), $r['Default']) : '');

            $columns[$k]['name'] = !empty($columns_translations[$k]) ? $columns_translations[$k] : $k;

            if (!empty($custom_functions[$k]))
            {
                $content = $custom_functions[$k]['body']($k, $value, static::$modelName);
            } elseif (in_array($k, $readonly))
            {
                $content = static::readonly($k, $value);
            } else
            {
                switch ($type)
                {
                    case 'bigint':
                    case 'int':
                    case 'smallint':
                    case 'tinyint':
                    case 'text':
                        $content = static::input($k, $value);
                        break;

//					case 'text':
//						$content = static::textarea($k, $value);
//					break;

                    default:
                        $content = static::input($k, $value);
                        break;
                }
            }

            $columns[$k]['content'] = $content;
        }

        $return .= '<form class="form-horizontal" role="form" id="' . str_replace('_', '-', static::$modelName) . '" method="post" enctype="multipart/form-data"><fieldset>';

        if (!empty($params[static::$primaryKey]))
        {
            $return .= '<input type="hidden" name="' . static::$modelName . '[' . static::$primaryKey . ']" id="' . str_replace('_', '-', static::$modelName) . '-' . static::$primaryKey . '" value="' . $params[static::$primaryKey] . '" />';
        }
        foreach ($columns as $column)
        {
            $return .= '<div class="form-group">';
            $return .= '<label class="col-sm-2 control-label">' . $column['name'] . '</label>';
            $return .= '<div class="col-sm-8">' . $column['content'] . '</div>';
            $return .= '</div>';
        }

        // customs
        foreach ($custom_functions as $column => $function)
        {
            if (in_array($column, array_keys($columns)))
            {
                continue;
            }

            $return .= '<div class="form-group">';
            $return .= $function['body'](null, $values, static::$modelName);
            $return .= '</div>';
        }

        $return .= '</fieldset><div class="form-group"><div class="col-sm-8 col-md-offset-2"><button type="submit" class="btn btn-success" value="' . str_replace('_', '-', static::$modelName) . '">Zapisz formularz</button></div></div></form>';

        //$return .= '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
        //$return .= '<script src="'.$GLOBALS['HP_host'].'/include/plugins/orimari/js/jquery.colorbox-min.js"></script>';
        //$return .= '<script src="'.$GLOBALS['HP_host'].'/include/plugins/orimari/js/admin.js"></script>';

        return $return;
    }

    public static function getColumns()
    {
        # Columns
        $sql = new Sql('hydraportal/custom', __FILE__, __LINE__);
        $sql->embed('query', "SHOW COLUMNS FROM " . static::$modelName . ";", __LINE__);
        $sql->execute(__LINE__);

        $columns = array();

        while ($r = $sql->fetchAssoc())
        {
            $columns[$r['Field']] = $r['Field'];
        }

        return $columns;
    }

    # JSON
    public static function buildJson($template = '', array $params = array())
    {
        $data = static::select($params);


    }

    # HANDLE RELATIONS
    private static function handleRelations(&$row)
    {

        if (!empty(static::$relation))
        {
            foreach (static::$relation as $k => $o)
            {
                switch ($o['relation'])
                {
                    # ONE TO MANY
                    case 'o2m':
                        $name = !empty($o['alias']) ? $o['alias'] : $o['class'];
                        $params = array(
                            'where' => array(
                                $o['field'] . ' = ' . $row[static::$primaryKey],
                            ),
                        );
                        $row[$name] = $o['class']::select($params);
                        break;

                    # MANY TO MANY
                    case 'm2m':
                        $name = !empty($o['alias']) ? $o['alias'] : $o['passthrough']['class'];
                        $passthrough_params = array(
                            'where' => array(
                                $o['field'] . ' = ' . $row[static::$primaryKey],
                            ),
                        );

                        $passthrough = array();

                        foreach ($o['passthrough']['class']::select($passthrough_params) as $e)
                        {
                            $data = $o['class']::get($e[$o['passthrough']['field']], null, true);
                            $passthrough[$data[$o['class']::$primaryKey]] = $data;
                        }

                        $row[$name] = $passthrough;
                        break;

                    # ONE TO ONE
                    default:
                        $name = !empty($o['alias']) ? $o['alias'] : $o['class'];
                        $row[$name] = $o['class']::get($row[$k]);
                        unset($row[$k]);
                        break;
                }
            }
        }

    }

} // /DbBase
