# The viewer.tf teletext viewer

This is a teletext viewer that extends on the work of irrelevantdotcom
that extended the original teletext editor by rawles.

 * https://github.com/irrelevantdotcom/edit-tf
 * https://github.com/rawles/edit-tf

A sample page exists that shows how you might provide a web-based set
of teletext type frames.

I've cut down a lot of the previous feature set (and more will be cut)
so the page is basically only listening to 0-9 and hjkl ( colour keys).

To create your own page set, simply fork and change the set of links
in the result - the data-{Red,blue,green,yellow} are page numbers that
will be jumped to.

The hrefs can simply be links to the edit.tf page

## The viewer is licenced under GPLv3.
The source code is commented throughout and licenced under the GNU 
General Public Licence v3.0, with additional requirements concerning 
minimisation of the source code. See the notice for more details.

Associated scripts and other tools are licenced under the same terms.

michaelkaye's changes are released also under the GPLv3.
