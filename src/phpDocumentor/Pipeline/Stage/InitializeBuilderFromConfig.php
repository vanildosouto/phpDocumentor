<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Pipeline\Stage;

use phpDocumentor\Descriptor\ApiSetDescriptor;
use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\Collection as PartialsCollection;
use phpDocumentor\Descriptor\DocumentationSetDescriptor;
use phpDocumentor\Descriptor\GuideSetDescriptor;
use phpDocumentor\Descriptor\VersionDescriptor;

final class InitializeBuilderFromConfig
{
    /** @var PartialsCollection<string> */
    private $partials;

    /**
     * @param PartialsCollection<string> $partials
     */
    public function __construct(PartialsCollection $partials)
    {
        $this->partials = $partials;
    }

    public function __invoke(Payload $payload) : Payload
    {
        $configuration = $payload->getConfig();

        $builder = $payload->getBuilder();
        $builder->createProjectDescriptor();
        $builder->setName($configuration['phpdocumentor']['title'] ?? '');
        $builder->setPartials($this->partials);
        $builder->setCustomSettings($configuration['phpdocumentor']['settings'] ?? []);

        foreach (($configuration['phpdocumentor']['versions'] ?? []) as $version) {
            $documentationSets = Collection::fromClassString(DocumentationSetDescriptor::class);

            foreach ($version['guides'] ?? [] as $guide) {
                $documentationSets->add(new GuideSetDescriptor('', $guide['source'], $guide['output']));
            }

            foreach ($version['api'] ?? [] as $api) {
                $documentationSets->add(new ApiSetDescriptor('', $api['source'], $api['output']));
            }

            $version = new VersionDescriptor($version['number'], $documentationSets);
            $builder->addVersion($version);
        }

        return $payload;
    }
}
