<?php

require_once dirname(__FILE__) . '/sfEvent.php';

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * sfEventDispatcher implements a dispatcher object.
 *
 * @see        http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework
 *
 * @package    symfony
 * @subpackage event_dispatcher
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfEventDispatcher.class.php 10631 2008-08-03 16:50:47Z fabien $
 */
class sfEventDispatcher
{
    protected
        $listeners = array();


    /**
     * Connects a listener to a given event name.
     *
     * @param string  $name      An event name
     * @param mixed   $listener  A PHP callable
     */
    public function connect($name, $listener)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = array();
        }

        $this->listeners[$name][] = $listener;
    }


    /**
     * Disconnects a listener for a given event name.
     *
     * @param string   $name      An event name
     * @param mixed    $listener  A PHP callable
     *
     * @return mixed false if listener does not exist, null otherwise
     */
    public function disconnect($name, $listener)
    {
        if (!isset($this->listeners[$name])) {
            return FALSE;
        }

        foreach ($this->listeners[$name] as $i => $callable) {
            if ($listener === $callable) {
                unset($this->listeners[$name][$i]);
            }
        }
    }


    /**
     * Notifies all listeners of a given event.
     *
     * @param sfEvent $event A sfEvent instance
     *
     * @return sfEvent The sfEvent instance
     */
    public function notify(sfEvent $event)
    {
        foreach ($this->getListeners($event->getName()) as $listener) {
            call_user_func($listener, $event);
        }

        return $event;
    }


    /**
     * Notifies all listeners of a given event until one returns a non null value.
     *
     * @param  sfEvent $event A sfEvent instance
     *
     * @return sfEvent The sfEvent instance
     */
    public function notifyUntil(sfEvent $event)
    {
        foreach ($this->getListeners($event->getName()) as $listener) {
            if (call_user_func($listener, $event)) {
                $event->setProcessed(TRUE);
                break;
            }
        }

        return $event;
    }


    /**
     * Filters a value by calling all listeners of a given event.
     *
     * @param  sfEvent  $event   A sfEvent instance
     * @param  mixed    $value   The value to be filtered
     *
     * @return sfEvent The sfEvent instance
     */
    public function filter(sfEvent $event, $value)
    {
        foreach ($this->getListeners($event->getName()) as $listener) {
            $value = call_user_func_array(
                $listener, array(
                    $event,
                    $value
                )
            );
        }

        $event->setReturnValue($value);

        return $event;
    }


    /**
     * Returns true if the given event name has some listeners.
     *
     * @param  string   $name    The event name
     *
     * @return Boolean true if some listeners are connected, false otherwise
     */
    public function hasListeners($name)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = array();
        }

        return (boolean)count($this->listeners[$name]);
    }


    /**
     * Returns all listeners associated with a given event name.
     *
     * @param  string   $name    The event name
     *
     * @return array  An array of listeners
     */
    public function getListeners($name)
    {
        if (!isset($this->listeners[$name])) {
            return array();
        }

        return $this->listeners[$name];
    }
}
