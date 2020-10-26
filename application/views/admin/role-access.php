
            <!-- Begin Page Content -->
            <div class="container-fluid">

            <!-- Page Heading -->
            <h1 class="h3 mb-4 text-gray-800"><?= $title;?></h1>

            
            <div class="row">
                <div class="col-lg-6">
                <?= $this->session->flashdata('message');?>
                <h3><span class="badge badge-primary">Role : <?= $role['role'];?></span></h3>
                <table class="table table-hover mt-3">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Access</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1;?>
                    <?php foreach($menu as $m):?>
                        <tr>
                        <th scope="row"><?= $i;?></th>
                        <td><?= $m['menu'];?></td>
                        <td>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" <?= check_access($role['id'],$m['id']);?> data-role="<?=$role['id']?>" data-menu="<?=$m['id']?>">
                            <!-- check_access() is helper function to make dynamic checkbox -->
                        </div>
                        </td>
                        </tr>
                        <?php $i++;?>
                    <?php endforeach;?>
                    </tbody>
                </table>
                </div>
            </div>

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

