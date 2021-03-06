<?php
/**
 * Provides an interface to define filters to be used inside a criteria
 *
 * @version    1.0
 * @package    database
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TFilter extends TExpression
{
    private $variable;
    private $operator;
    private $value;
    private $value2;
    
    /**
     * Class Constructor
     * 
     * @param  $variable = variable
     * @param  $operator = operator (>, <, =, BETWEEN)
     * @param  $value    = value to be compared
     * @param  $value2   = second value to be compared (between)
     */
    public function __construct($variable, $operator, $value, $value2 = NULL)
    {
        // store the properties
        $this->variable = $variable;
        $this->operator = $operator;
        
        // transform the value according to its type
        $this->value    = $this->transform($value);
        
        if ($value2)
        {
            $this->value2 = $this->transform($value2);
        }
    }
    
    /**
     * Transform the value according to its PHP type
     * before send it to the database
     * @param $value Value to be transformed
     * @return       Transformed Value
     */
    private function transform($value)
    {
        // if the value is an array
        if (is_array($value))
        {
            $foo = array();
            // iterate the array
            foreach ($value as $x)
            {
                // if the value is an integer
                if (is_numeric($x))
                {
                    $foo[] = $x;
                }
                else if (is_string($x))
                {
                    // if the value is an string, add quotes
                    $foo[] = "'$x'";
                }
                else if (is_bool($x))
                {
                    $foo[] = ($x) ? 'TRUE' : 'FALSE';
                }
            }
            // convert the array into a string, splitted by ","
            $result = '(' . implode(',', $foo) . ')';
        }
        // if the value is a subselect (must not be escaped as string)
        else if (substr($value,0,7) == '(SELECT')
        {
            $result = "$value";
        }
        // if the value must not be escaped (NOESC in front)
        else if (substr($value,0,6) == 'NOESC:')
        {
            $result = substr($value,6);
        }
        // if the value is a string
        else if (is_string($value))
        {
            // add quotes
            $result = "'$value'";
        }
        // if the value is NULL
        else if (is_null($value))
        {
            // the result is 'NULL'
            $result = 'NULL';
        }
        // if the value is a boolean
        else if (is_bool($value))
        {
            // the result is 'TRUE' of 'FALSE'
            $result = $value ? 'TRUE' : 'FALSE';
        }
        // if the value is a TSqlStatement object
        else if ($value instanceof TSqlStatement)
        {
            // the result is the return of the getInstruction()
            $result = '(' . $value->getInstruction() . ')';
        }
        else
        {
            $result = $value;
        }
        
        // returns the result
        return $result;
    }
    
    /**
     * Return the filter as a string expression
     * @return  A string containing the filter
     */
    public function dump()
    {
        if ($this->value2)
        {
            // concatenated the expression
            return "{$this->variable} {$this->operator} {$this->value} AND {$this->value2}";
        }
        else
        {
            // concatenated the expression
            return "{$this->variable} {$this->operator} {$this->value}";
        }
    }
}
?>
