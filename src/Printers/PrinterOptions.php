<?php

namespace Stillat\BladeParser\Printers;

/**
 * Class PrinterOptions.
 *
 * A collection of various printing options.
 *
 * @since 1.0.0
 */
class PrinterOptions
{
    /**
     * The current new-line style.
     * @var string
     */
    protected $newLineType = "\n";

    /**
     * Sets the new-line style.
     *
     * @param string $style The new-line style.
     */
    public function setNewLineStyle($style)
    {
        $this->newLineType = $style;
    }

    /**
     * Gets the current new-line style.
     *
     * @return string
     */
    public function getNewLineStyle()
    {
        return $this->newLineType;
    }
}
