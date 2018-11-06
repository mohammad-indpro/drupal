<?php

namespace Drupal\KernelTests\Core\Plugin;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that contexts work properly with the typed data manager.
 *
 * @coversDefaultClass \Drupal\Core\Plugin\Context\Context
 * @group Context
 */
class ContextTypedDataTest extends KernelTestBase {

  /**
   * Tests that contexts can be serialized.
   */
  public function testSerialize() {
    $definition = new ContextDefinition('any');
    $data_definition = DataDefinition::create('string');
    $typed_data = new StringData($data_definition);
    $typed_data->setValue('example string');
    $context = new Context($definition, $typed_data);
    // getContextValue() will cause the context to reference the typed data
    // manager service.
    $value = $context->getContextValue();
    $context = serialize($context);
    $context = unserialize($context);
    $this->assertSame($value, $context->getContextValue());
  }

  /**
   * Tests that getting a context value does not throw fatal errors.
   *
   * This test ensures that the typed data manager is set correctly on the
   * Context class.
   *
   * @covers ::getContextValue
   */
  public function testGetContextValue() {
    $data_definition = DataDefinition::create('string');
    $typed_data = new StringData($data_definition);
    $typed_data->setValue('example string');

    // Prepare a container that holds the typed data manager mock.
    $typed_data_manager = $this->prophesize(TypedDataManagerInterface::class);
    $typed_data_manager->getCanonicalRepresentation($typed_data)
      ->shouldBeCalledOnce()
      ->will(function () use ($typed_data) {
        return $typed_data->getValue();
      });
    $this->container->set('typed_data_manager', $typed_data_manager->reveal());

    $definition = new ContextDefinition('any');
    $context = new Context($definition, $typed_data);
    $value = $context->getContextValue();
    $this->assertSame($value, $typed_data->getValue());
  }

}
