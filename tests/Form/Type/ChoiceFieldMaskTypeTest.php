<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChoiceFieldMaskTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $options = $this->resolveOptions([
            'map' => [
                'foo' => ['field1', 'field2'],
                'bar' => ['field3'],
            ],
        ]);

        static::assertSame(['foo' => ['field1', 'field2'], 'bar' => ['field3']], $options['map']);
    }

    public function testGetDefaultOptions2(): void
    {
        $options = $this->resolveOptions([]);

        static::assertSame(['map' => []], $options);
    }

    /**
     * @phpstan-return array<array{mixed}>
     */
    public function setAllowedTypesProvider(): array
    {
        return [
            'null' => [null],
            'integer' => [1],
            'boolean' => [false],
            'string' => ['string'],
            'class' => [new \stdClass()],
        ];
    }

    /**
     * @param mixed $map
     *
     * @dataProvider setAllowedTypesProvider
     */
    public function testSetAllowedTypes($map): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessageMatches('/The option "map" with value .* is expected to be of type "array", but is of type ".*"/');

        $this->resolveOptions(['map' => $map]);
    }

    public function testGetBlockPrefix(): void
    {
        $type = new ChoiceFieldMaskType();
        static::assertSame('sonata_type_choice_field_mask', $type->getBlockPrefix());
    }

    public function testGetParent(): void
    {
        $type = new ChoiceFieldMaskType();
        static::assertSame(ChoiceType::class, $type->getParent());
    }

    public function testBuildView(): void
    {
        $choiceFieldMaskType = new ChoiceFieldMaskType();

        $view = $this->createStub(FormView::class);

        $choiceFieldMaskType->buildView(
            $view,
            $this->createStub(FormInterface::class),
            [
                'map' => [
                    'choice_1' => ['field1', 'field2'],
                    'choice_2' => ['field__3', 'field.4'],
                    'choice_3' => ['field1', 'field5'],
                ],
            ]
        );

        $expectedAllFields = [
            'field1',
            'field2',
            'field____3',
            'field__4',
            'field5',
        ];

        $expectedMap = [
            'choice_1' => [
                'field1',
                'field2',
            ],
            'choice_2' => [
                'field____3',
                'field__4',
            ],
            'choice_3' => [
                'field1',
                'field5',
            ],
        ];

        static::assertSame($expectedAllFields, array_values($view->vars['all_fields']), '"all_fields" is not as expected');
        static::assertSame($expectedMap, $view->vars['map'], '"map" is not as expected');
    }

    public function testBuildViewWithFaultyMapValues(): void
    {
        $choiceFieldMaskType = new ChoiceFieldMaskType();

        $view = $this->createStub(FormView::class);

        $choiceFieldMaskType->buildView(
            $view,
            $this->createStub(FormInterface::class),
            ['map' => [
                'int' => 1,
                'string' => 'string',
                'boolean' => false,
                'array' => ['field_1', 'field_2'],
                'empty_array' => [],
                'class' => new \stdClass(),
            ]]
        );

        $expectedAllFields = ['field_1', 'field_2'];
        $expectedMap = [
            'array' => ['field_1', 'field_2'],
        ];

        static::assertSame($expectedAllFields, array_values($view->vars['all_fields']), '"all_fields" is not as expected');
        static::assertSame($expectedMap, $view->vars['map'], '"map" is not as expected');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function resolveOptions(array $options): array
    {
        $type = new ChoiceFieldMaskType();
        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        return $optionResolver->resolve($options);
    }
}
