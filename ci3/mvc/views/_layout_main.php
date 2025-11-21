<?php $this->load->view("components/page_header"); ?>
<?php $this->load->view("components/page_topbar"); ?>
<?php $this->load->view("components/page_menu"); ?>

        <aside class="right-side">
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <?php $this->load->view($subview); ?>
                    </div>
                </div>
            </section>
        </aside>

        <footer class="main-footer">
          	<strong>Copyright &copy; Shulelabs</strong>
        </footer>
<?php $this->load->view("components/page_footer"); ?>
