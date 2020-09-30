<?php

require_once __DIR__ . '/../vendor/autoload.php';

use StatusQuo\FSM\DistributedTransitionLogicFiniteStateMachineInterface;
use StatusQuo\FSM\Factory\FiniteStateMachineFactory;
use StatusQuo\FSM\State\Factory\NewStateFactory;
use StatusQuo\FSM\State\StateInterface;

class StateA implements StateInterface {
  public function process(
    DistributedTransitionLogicFiniteStateMachineInterface $FSM,
    $input = null
  ) {
    echo PHP_EOL;
    echo json_encode(
      ['state' => get_called_class(), 'input' => $input],
      JSON_PRETTY_PRINT
    );
    echo PHP_EOL;
    $FSM->setState(StateB::class);
    return get_called_class() . " return value";
  }
}

class StateB implements StateInterface {
  public function process(
    DistributedTransitionLogicFiniteStateMachineInterface $FSM,
    $input = null
  ) {
    echo PHP_EOL;
    echo json_encode(
      ['state' => get_called_class(), 'input' => $input],
      JSON_PRETTY_PRINT
    );
    echo PHP_EOL;
    $FSM->setState(StateC::class);
    return get_called_class() . " return value";
  }
}

class StateC implements StateInterface {
  public function process(
    DistributedTransitionLogicFiniteStateMachineInterface $FSM,
    $input = null
  ) {
    echo PHP_EOL;
    echo json_encode(
      ['state' => get_called_class(), 'input' => $input],
      JSON_PRETTY_PRINT
    );
    echo PHP_EOL;
    $FSM->setState(StateA::class);
    return get_called_class() . " return value";
  }
}

$factory = new FiniteStateMachineFactory(new NewStateFactory());
$FSM = $factory->make(StateA::class);

echo PHP_EOL;
$returnValue = $FSM->process("Input 1");
echo json_encode(['returnValue' => $returnValue], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
$returnValue = $FSM->process("Input 2");
echo json_encode(['returnValue' => $returnValue], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
$returnValue = $FSM->process("Input 3");
echo json_encode(['returnValue' => $returnValue], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
$returnValue = $FSM->process("Input 4");
echo json_encode(['returnValue' => $returnValue], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
$returnValue = $FSM->process("Input 5");
echo json_encode(['returnValue' => $returnValue], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
