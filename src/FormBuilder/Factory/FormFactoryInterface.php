<?php

namespace FormBuilderBundle\Factory;

interface FormFactoryInterface
{
    public function createForm();

    public function getFormById($id);

    public function getAllForms();

    public function getFormIdByName($name);

    public function createFormField();
}