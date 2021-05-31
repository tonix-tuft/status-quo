<?php

/*
 * Copyright (c) 2021 Anton Bagdatyev (Tonix)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace StatusQuo\FSM\Factory;

use StatusQuo\FSM\Factory\FiniteStateMachineFactoryInterface;
use StatusQuo\FSM\State\StateInterface;
use StatusQuo\FSM\State\Factory\StateFactoryInterface;
use StatusQuo\FSM\FiniteStateMachineInterface;

/**
 * The implementation of a finite-state machine factory.
 *
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
class FiniteStateMachineFactory implements FiniteStateMachineFactoryInterface {
  /**
   * @var StateFactoryInterface
   */
  protected $factory;

  /**
   * @var bool
   */
  protected $cacheStates;

  /**
   * Constructs a new factory.
   *
   * @param StateFactoryInterface $factory A state factory.
   * @param $cacheStates Whether or not to cache states.
   */
  public function __construct(
    StateFactoryInterface $factory,
    $cacheStates = true
  ) {
    $this->factory = $factory;
    $this->cacheStates = $cacheStates;
  }

  /**
   * {@inheritdoc}
   */
  public function make($initialState): FiniteStateMachineInterface {
    return new class(
      $initialState,
      $this->factory,
      $this->cacheStates
    ) implements FiniteStateMachineInterface {
      /**
       * @var array
       */
      protected $cachedStatesMap;

      /**
       * @var StateFactoryInterface
       */
      protected $factory;

      /**
       * @var bool
       */
      protected $cacheStates;

      /**
       * @var StateInterface
       */
      protected $state;

      /**
       * @var array
       */
      protected $data;

      /**
       * @var array
       */
      protected $onDataCallables;

      /**
       * Constructs a new finite-state machine.
       *
       * @param string|StateInterface $initialState The initial state of the state machine.
       * @param StateFactoryInterface $factory The state factory.
       * @param bool $cacheStates Whether or not to cache states.
       */
      public function __construct(
        $initialState,
        StateFactoryInterface $factory,
        $cacheStates = true
      ) {
        $this->factory = $factory;
        $this->cacheStates = $cacheStates;
        $this->cachedStatesMap = [];
        $this->data = [];
        $this->onDataCallables = [];

        $state = $this->getStateInstance($initialState);
        $this->state = $state;
      }

      /**
       * Obtains a state instance of the state machine.
       *
       * @param string|StateInterface $state The state instance of the state machine to obtain.
       * @return StateInterface The state instance.
       */
      protected function getStateInstance($state): StateInterface {
        if ($state instanceof StateInterface) {
          return $state;
        } else {
          if (isset($this->cachedStatesMap[$state])) {
            return $this->cachedStatesMap[$state];
          }
          $stateInstance = $this->factory->make($state);
          if ($this->cacheStates && !isset($this->cachedStatesMap[$state])) {
            $this->cachedStatesMap[$state] = $stateInstance;
          }
          return $stateInstance;
        }
      }

      /**
       * Triggers the callables bound to a data key after they are changed.
       *
       * @param string $key The key of the data.
       * @param mixed $oldData The old data of the corresponding key before they changed.
       * @return void
       */
      protected function triggerOnDataCallables($key, $oldData) {
        if (isset($this->onDataCallables[$key])) {
          $callables = $this->onDataCallables[$key];
          $currentData = $this->data[$key] ?? null;
          foreach ($callables as $callable) {
            $callable($currentData, $oldData);
          }
        }
      }

      /**
       * {@inheritdoc}
       */
      public function process($input = null) {
        return $this->state->process($this, $input);
      }

      /**
       * {@inheritdoc}
       */
      public function setState($state) {
        /* @var $nextState StateInterface */
        $nextState = $this->getStateInstance($state);
        $this->state = $nextState;
      }

      /**
       * {@inheritdoc}
       */
      public function setData($key, $data) {
        $oldData = $oldData = $this->data[$key] ?? null;
        if (is_callable($data)) {
          $dataToSet = $data($oldData);
          $this->data[$key] = $dataToSet;
        } else {
          $this->data[$key] = $data;
        }
        $this->triggerOnDataCallables($key, $oldData);
      }

      /**
       * {@inheritdoc}
       */
      public function getData($key) {
        if (array_key_exists($key, $this->data)) {
          return $this->data[$key];
        }
        return null;
      }

      /**
       * {@inheritdoc}
       */
      public function onData($key, callable $callable) {
        if (!isset($this->onDataCallables[$key])) {
          $this->onDataCallables[$key] = [];
        }
        $this->onDataCallables[$key][] = $callable;
      }
    };
  }
}
