<?php

/*
 * This file is part of the BootstrapBundle package.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Demo\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * FormType Class.
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class FormType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder,array $options)
    {
        $builder->add('text','text');
        
        $builder->add('textarea','textarea');
        
        $builder->add('email','email');
        
        
    }
    
    public function getName()
    {
        return "form";
    }    
}