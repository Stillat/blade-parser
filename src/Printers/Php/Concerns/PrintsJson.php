<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsJson
{

    /**
     * The default JSON encoding options.
     *
     * @var int
     */
    private $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;


    protected function print_json(Node $node)
    {

        $parts = explode(',', $node->innerContent());

        $options = isset($parts[1]) ? trim($parts[1]) : $this->encodingOptions;

        $depth = isset($parts[2]) ? trim($parts[2]) : 512;

        return "<?php echo json_encode($parts[0], $options, $depth) ?>";
    }

}