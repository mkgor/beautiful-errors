<?php


namespace BeautifulErrors;

use Coduo\ToString\StringConverter;
use Highlighter\Highlighter;
use Highlighter\Renderer\StandardLineRenderer;

/**
 * Class Handler
 *
 * @package BeautifulErrors
 */
class Handler
{
    /** @var Highlighter */
    private static $highlighter;

    /**
     * @var StandardLineRenderer
     */
    private static $renderer;

    const LEFT_PADDING = "    ";

    /**
     * @param int $errorTypes
     */
    public static function setupHandlers($errorTypes = E_ERROR)
    {
        set_error_handler([Handler::class, 'errorHandler'], $errorTypes);
        set_exception_handler([Handler::class, 'exceptionHandler']);

        self::$highlighter = new Highlighter();
        self::$highlighter->setTheme(new HighlighterTheme());
        self::$renderer = self::$highlighter->lineRenderer;
    }

    /**
     * @param $errorCode
     * @param $message
     * @param $file
     * @param $line
     *
     * @throws \Exception
     */
    public static function errorHandler($errorCode, $message, $file, $line)
    {
        switch ($errorCode) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                break;
            case E_STRICT:
                $error = 'Strict';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $error = 'Deprecated';
                break;
            default :
                $error = 'Unknown error';
                break;
        }

        echo self::buildBaseError($error, $message, $file, $line);
    }

    /**
     * @param \Exception $exception
     *
     * @throws \Exception
     */
    public static function exceptionHandler(\Throwable $exception)
    {
        echo PHP_EOL;
        echo self::buildBaseError(get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        echo self::buildStackTrace($exception->getTrace());
        echo PHP_EOL;
    }

    /**
     * @param $string
     *
     * @return string
     */
    private static function printText($string, $styleCode = [])
    {
        return sprintf("%s%s%s", self::$renderer->buildStyleCode($styleCode), $string, self::$renderer::ANSI_RESET_STYLES);
    }

    /**
     * @param $title
     * @param $message
     * @param $file
     * @param $line
     *
     * @return string
     * @throws \Exception
     */
    private static function buildBaseError($title, $message, $file, $line)
    {
        $output = '';

        $output .= self::LEFT_PADDING . self::printText(sprintf(" %s ", $title), ['bg_red', 'white', 'bold']);
        $output .= ' : ';
        $output .= self::printText($message, ['yellow']);
        $output .= PHP_EOL . PHP_EOL;
        $output .= self::LEFT_PADDING . 'at ';
        $output .= self::printText($file, ['green']);
        $output .= PHP_EOL;

        $output .= self::LEFT_PADDING . " " . str_replace("\n", "\n " . self::LEFT_PADDING,  self::$highlighter->getSnippet($file, $line, 9));

        return $output;
    }

    /**
     * @param array $trace
     *
     * @return string
     */
    private static function buildStackTrace($trace = [])
    {
        $output = '';

        if($trace)
            $output .= self::printText("\n".self::LEFT_PADDING ."Call trace: ", ['yellow']);

        foreach (array_reverse($trace) as $i => $traceItem)
        {
            $output .= PHP_EOL . PHP_EOL .self::LEFT_PADDING;
            $output .= sprintf("%s%d %s", self::$renderer->buildStyleCode(['blue']), $i + 1, self::$renderer::ANSI_RESET_STYLES);
            $output .= self::LEFT_PADDING;
            $output .= self::printText($traceItem['class'],['yellow']);
            $output .= self::printText($traceItem['type'],['white']);
            $output .= self::printText($traceItem["function"],['light_gray']);

            $output .= "(";

            $argArray = [];

            foreach($traceItem['args'] as $arg) {
                if(is_string($arg)) {
                    $item = sprintf("\"%s\"", $arg);
                } else {
                    $item = (string)(new StringConverter($arg));
                }

                switch(gettype($arg)) {
                    case 'string':
                        $argArray[] = self::printText($item, ['green']);
                        break;
                    case 'int':
                        $argArray[] = self::printText($item, ['blue']);
                        break;
                    default:
                        $argArray[] = self::printText($item, ['light_cyan']);
                        break;
                }
            }

            $output .= implode(",", $argArray);

            $output .= ")";

            $output .= PHP_EOL . self::LEFT_PADDING . self::LEFT_PADDING . "  ";
            $output .= self::printText($traceItem['file'] . ' : ' . $traceItem['line'], ['green']);
        }

        return $output;
    }
}