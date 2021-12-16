<?php
namespace app\services\manage;

use app\repository\PermissionRepository;

class PermissionServices
{
	protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository){
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']) {
        return $this->permissionRepository->find($id, $columns);
    }

    /**
     * 存储一条数据
     *
     * @param array $data
     * @return void
     */
    public function store(array $data) {
        $role = $this->permissionRepository->create($data);
        return $role;
    }

    /**
     * 更新一条数据
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function update(string $id, array $data) {
        return $this->permissionRepository->update($data, $id);
    }

    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     * 
     * @return bool
     */
    public function delete(int $id) {
        return $this->permissionRepository->delete($id);
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->permissionRepository->all($columns);
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search        [搜索关键词]
     * 
     * @return array
     */
    public function listForTotal(string $search)
    {
        return $this->permissionRepository->listForTotal($search);
    }

    /**
     * 数据分页
     *
     * @param string $search 
     * @param integer $start
     * @param integer $length
     * 
     * @return void
     */
    public function listForPage(string $search, int $start, int $length)
    {
        $permissions = $this->permissionRepository->listForPage($search, $start, $length);

        $list = array();

        foreach($permissions as $key=>$node) {
            $item = array('authorityId'=>$node['id'], 'authorityName'=>$node['description'], 'authority'=>$node['name'], 'menuUrl'=>$node['menuUrl'], 'parentId'=>$node['parent_id'], 'isMenu'=>$node['display_menu'], 'orderNumber'=>$node['sort_order'], 'menuIcon'=>$node['icon']);
            $list[] = $item;
        }

        return $list;
    }

    /**
     * 菜单数据
     *
     * @param array $columns
     * @return void
     */
    public function menus()
    {
        $permissions = $this->permissionRepository->all()->toArray();

        $list = array();

        if (!empty($permissions)) {
            $list = $this->menuTreeview(array_pluck($permissions, 'parent_id', 'id'), 0);
        }

        return $list;
    }

    /**
     * 菜单数据节点
     * 
     * @param array $permissions  [权限数据]
     * @param int   $nodeid [节点ID]
     * 
     * @param int   $defalutMeus [默认选择状态]
     */
    protected function menuTreeview(array $permissions, int $nodeid)
    {
        $json = array();

        foreach ($permissions[$nodeid] as $pkey => $node) {
            $json_item = array('name'=>$node['permission_name']);

            if($node['parent_id'] == 0) {
                $json_item['url'] = $node['javascript:;'];
                $json_item['icon'] = $node['icon'];
            }
            else
            {
                $json_item['menuUrl'] = '#/' . $node['menuUrl'];
            }

            if (!empty($permissions[$node['id']])) {
                $json_item['subMenus'] = $this->treeview($permissions, $node['id']);
            }
            else
            {
                $permissionNodes = array();

                if (!empty($permissions[$node['id']])) {
                    foreach ($permissions[$node['id']] as $key => $value) {
                        $permissionNodes[] = array('name'=>$value['permission_name']);
                    }

                    $json_item['subMenus'] = $permissionNodes;
                }
            }

            $json[] = $json_item;
        }

        return $json;
    }

    /**
     * 获取全部菜单
     * @return [type] [description]
     */
    public function getMenuOption(array $list)
    {
        $html = '';

        if (!empty($list)) {
            $cagegorys = array_pluck($list, 'parent_id', 'id');

            foreach(array_first($cagegorys) as $row) {
                $html .= $this->createOptionHtml($cagegorys, $row);
            }
        }

        return $html;
    }

    /**
     * 创建菜单选项
     * @param  array  $list     [分类]
     * @param  array  $row     [description]
     * @return [type]            [description]
     */
    protected function createOptionHtml(array $list, array $row)
    {
        $option_html = "";

        $parent_id = $row['id'];

        if(isset($list[$parent_id]))
        {
            foreach ($list[$parent_id] as $k => $v) {
                $option_html .= $this->createOptionHtml($list, $v);
            }
        }

        $option_html = '<option value="' . $row['id'] . '">' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $row['level']) . $row['description'] . '</option>' . $option_html;

        return $option_html;
    }
}