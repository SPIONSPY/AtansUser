<?php
namespace AtansUser\Controller;

use AtansUser\Entity\Role;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;

class RoleAdminController extends AbstractActionController
{
    /**
     * Flash messenger namespace
     *
     * @var string
     */
    const FM_NS = 'atansuser-role-admin-index';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $entities = array(
        'Role' => 'AtansUser\Entity\Role',
    );

    /**
     * @var Form
     */
    protected $roleAddForm;

    /**
     * @var Form
     */
    protected $roleEditForm;

    public function indexAction()
    {
        $entityManager = $this->getEntityManager();

        $returns = array(
            'roles' => $entityManager->getRepository($this->entities['Role'])->findAll(),
            'flashMessages' => null,
        );

        $flashMessenger = $this->flashMessenger()->setNamespace(self::FM_NS);
        if ($flashMessages = $flashMessenger->getMessages()) {
            $returns['flashMessages'] = $flashMessages;
        }

        return $returns;
    }

    public function addAction()
    {
        $entityManager = $this->getEntityManager();
        $translator    = $this->getServiceLocator()->get('Translator');

        $role = new Role();

        $form = $this->getRoleAddForm();
        $form->bind($role);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($role);
                $entityManager->flush();

                $this->flashMessenger()
                    ->setNamespace(self::FM_NS)
                    ->addMessage(sprintf(
                        $translator->translate("新增角色成功 '%s'"),
                        $role->getName()
                    ));

                return $this->redirect()->toRoute('zfcadmin/user/role');
            }
        }

        return array(
            'form' => $form,
        );
    }

    public function editAction()
    {
        $entityManager = $this->getEntityManager();
        $id            = (int) $this->params()->fromRoute('id', 0);
        $translator    = $this->getServiceLocator()->get('Translator');


        $role = $entityManager->find($this->entities['Role'], $id);
        if (!$role) {
            $this->flashMessenger()
                 ->setNamespace(self::FM_NS)
                 ->addMessage(sprintf(
                    $translator->translate("找不到角色 '%d'"),
                    $id
                ));

            return $this->redirect()->toRoute('zfcadmin/user/role');
        }

        $form = $this->getRoleEditForm();
        $form->bind($role);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (!isset($data['permissions'])) {
                $data['permissions'] = array();
            }
            $form->setData($data);

            if ($form->isValid()) {
                if ($entityManager->getRepository($this->entities['Role'])->noNameExists($role->getName(), $role->getId())) {
                    $entityManager->persist($role);
                    $entityManager->flush();

                    $this->flashMessenger()
                        ->setNamespace(self::FM_NS)
                        ->addMessage(sprintf(
                            $translator->translate("修改角色成功 '%s'"),
                            $role->getName()
                        ));

                    return $this->redirect()->toRoute('zfcadmin/user/role');
                } else {
                    $form->get('name')->setMessages(array(sprintf(
                        $translator->translate("角色'%s'已存在"),
                        $role->getName()
                    )));
                }

            }
        }

        return array(
            'form' => $form,
            'role' => $role,
        );
    }

    public function deleteAction()
    {
        $entityManager = $this->getEntityManager();
        $id            = (int) $this->params()->fromRoute('id', 0);
        $translator    = $this->getServiceLocator()->get('Translator');

        $role = $this->getEntityManager()->find($this->entities['Role'], $id);
        if (!$role) {
            $this->flashMessenger()
                ->setNamespace(self::FM_NS)
                ->addMessage(sprintf(
                    $translator->translate("找不到角色 '%d'"),
                    $id
                ));

            return $this->redirect()->toRoute('zfcadmin/user/role');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $delete = $request->getPost('delete');
            if ($delete == 'Yes') {
                $entityManager->remove($role);
                $entityManager->flush();

                $this->flashMessenger()
                    ->setNamespace(self::FM_NS)
                    ->addMessage(sprintf(
                        $translator->translate("刪除角色成功 '%s'"),
                        $role->getName()
                    ));

                return $this->redirect()->toRoute('zfcadmin/user/role');
            }
        }

        return array(
            'role' => $role,
        );
    }

    /**
     * Get entityManager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager instanceof EntityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    /**
     * Set entityManager
     *
     * @param  EntityManager $entityManager
     * @return RoleController
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * Get roleForm
     *
     * @return Form
     */
    public function getRoleAddForm()
    {
        if (!$this->roleAddForm instanceof Form) {
            $this->setRoleAddForm($this->getServiceLocator()->get('atansuser_role_add_form'));
        }
        return $this->roleAddForm;
    }

    /**
     * Set roleForm
     *
     * @param  Form $roleAddForm
     * @return RoleController
     */
    public function setRoleAddForm(Form $roleAddForm)
    {
        $this->roleAddForm = $roleAddForm;
        return $this;
    }

    /**
     * Get roleEditForm
     *
     * @return Form
     */
    public function getRoleEditForm()
    {
        if (!$this->roleEditForm instanceof Form) {
            $this->setRoleEditForm($this->getServiceLocator()->get('atansuser_role_edit_form'));
        }
        return $this->roleEditForm;
    }

    /**
     * Set roleEditForm
     *
     * @param  Form $roleEditForm
     * @return RoleController
     */
    public function setRoleEditForm($roleEditForm)
    {
        $this->roleEditForm = $roleEditForm;
        return $this;
    }
}
