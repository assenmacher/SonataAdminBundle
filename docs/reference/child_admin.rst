Create child admins
-------------------

Let us say you have a ``PlaylistAdmin`` and a ``VideoAdmin``. You can
optionally declare the ``VideoAdmin`` to be a child of the ``PlaylistAdmin``.
This will create new routes like, for example, ``/playlist/{id}/video/list``,
where the videos will automatically be filtered by post.

To do this, you first need to call the ``addChild`` method in your ``PlaylistAdmin``
service configuration with two arguments, the child admin name (in this case
``VideoAdmin`` service) and the Entity field that relates our child Entity with
its parent:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        App\Admin\VideoAdmin:
            # tags, calls, etc

        App\Admin\PlaylistAdmin:
            calls:
                - [addChild, ['@App\Admin\VideoAdmin', 'playlist']]
                # Or `[addChild, ['@App\Admin\VideoAdmin']]` if there is no
                # field to access the Playlist from the Video entity

    .. code-block:: xml

        <!-- config/services.xml -->

        <service id="App\Admin\VideoAdmin">
            <!-- tags, calls, etc -->
        </service>

        <service id="App\Admin\PlaylistAdmin">
            <!-- ... -->

            <call method="addChild">
                <argument type="service" id="App\Admin\VideoAdmin"/>
                <argument>playlist</argument>
            </call>
        </service>

To display the ``VideoAdmin`` extend the menu in your ``PlaylistAdmin``
class::

    namespace App\Admin;

    use Knp\Menu\ItemInterface as MenuItemInterface;
    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Admin\AdminInterface;

    final class PlaylistAdmin extends AbstractAdmin
    {
        protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
        {
            if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
                return;
            }

            $admin = $this->isChild() ? $this->getParent() : $this;
            $id = $admin->getRequest()->get('id');

            $menu->addChild('View Playlist', $admin->generateMenuUrl('show', ['id' => $id]));

            if ($this->isGranted('EDIT')) {
                $menu->addChild('Edit Playlist', $admin->generateMenuUrl('edit', ['id' => $id]));
            }

            if ($this->isGranted('LIST')) {
                $menu->addChild('Manage Videos', $admin->generateMenuUrl('App\Admin\VideoAdmin.list', ['id' => $id]));
            }
        }
    }

It also possible to set a dot-separated value, like ``post.author``,
if your parent and child admins are not directly related.

Be wary that being a child admin is optional, which means that regular
routes will be created regardless of whether you actually need them
or not. To get rid of them, you may override the ``configureRoutes`` method::

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Route\RouteCollectionInterface;

    final class VideoAdmin extends AbstractAdmin
    {
        protected function configureRoutes(RouteCollectionInterface $collection): void
        {
            if ($this->isChild()) {
                return;
            }

            // This is the route configuration as a parent
            $collection->clear();

        }
    }

You can nest admins as deep as you wish.

Let's say you want to add comments to videos.

You can then add your ``CommentAdmin`` admin service as a child of
the ``VideoAdmin`` admin service.

Finally, the admin interface will look like this:

.. figure:: ../images/child_admin.png
   :align: center
   :alt: Child admin interface
   :width: 700px
