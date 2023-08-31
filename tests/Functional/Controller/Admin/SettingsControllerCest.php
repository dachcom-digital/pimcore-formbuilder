<?php

namespace DachcomBundle\Test\Functional\Controller\Admin;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\TestFormBuilder;

class SettingsControllerCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testTreeWithoutFormGroupsAction(FunctionalTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/settings/get-tree');

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'id'   => $form->getId(),
                'text' => $form->getName(),
                'leaf' => true
            ]
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTreeWithFormGroupsAction(FunctionalTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->setGroup('group1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/settings/get-tree');

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'id'       => 'group1',
                'text'     => 'group1',
                'leaf'     => false,
                'children' => [
                    [
                        'id'   => $form->getId(),
                        'text' => $form->getName(),
                        'leaf' => true,
                    ]
                ]
            ]
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTreeWithMixedFormGroupsAction(FunctionalTester $I)
    {
        $testFormBuilder1 = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form1 = $I->haveAForm($testFormBuilder1);

        $testFormBuilder2 = (new TestFormBuilder('dachcom_test_2'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->setGroup('group1')
            ->addFormFieldSubmitButton('submit');

        $form2 = $I->haveAForm($testFormBuilder2);

        $testFormBuilder3 = (new TestFormBuilder('dachcom_test_3'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->setGroup('group1')
            ->addFormFieldSubmitButton('submit');

        $form3 = $I->haveAForm($testFormBuilder3);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/settings/get-tree');

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'id'   => $form1->getId(),
                'text' => $form1->getName(),
                'leaf' => true
            ],
            [
                'id'       => 'group1',
                'text'     => 'group1',
                'leaf'     => false,
                'children' => [
                    [
                        'id'   => $form2->getId(),
                        'text' => $form2->getName(),
                        'leaf' => true,
                    ],
                    [
                        'id'   => $form3->getId(),
                        'text' => $form3->getName(),
                        'leaf' => true,
                    ]
                ]
            ]
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFindFormDependenciesAction(FunctionalTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test');
        $dummyDocument = $I->haveAPageDocument('content-test-page');

        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/settings/get-form-dependencies?formId=1&page=1&start=0&limit=1');

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'total'     => 1,
            'documents' => [
                [
                    'id'      => $document->getId(),
                    'type'    => 'document',
                    'subtype' => 'page',
                    'path'    => $document->getFullPath()
                ]
            ]
        ]);
    }
}
