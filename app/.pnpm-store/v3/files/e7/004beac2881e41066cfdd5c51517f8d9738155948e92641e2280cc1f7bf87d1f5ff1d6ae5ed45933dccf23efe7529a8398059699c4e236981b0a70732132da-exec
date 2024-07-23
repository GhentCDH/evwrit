# UGent

Static front-end templates for the UGent website based on [Bootstrap v3.3.7](http://getbootstrap.com).

## Usage

All files that are needed to apply the new styling of UGent websites are located in the [static](static) directory. If no further development is required, it suffices to copy this directory and include the files as demonstrated in [example.html](examples/example.html).

## Development

### Requirements

- [Node v10.13+](https://nodejs.org/en/)

### Installing

#### Npm dependencies

Use npm to install the dependencies by running `$ npm install` in the project root.

#### Gulp

If you don't have a global version of Gulp yet also run `$ (sudo) npm install gulp gulp-cli --global`. This allows you to run the `$ gulp`
command which is required to build the project.

### Building

To build the project the following steps need to be done:

- `$ gulp` to clean the `static` directory and process the JavaScript, font and CSS files.
- `$ gulp watch` to trigger a rebuild when any of the JavaScript or sass source files change. It is recommended to run `$ gulp` once before this command.

The result is in the `static` directory.

### Developing
CSS is compiled from SCSS which lives in the [sass](sass) folder.

Like Bootstrap, certain styles can be achieved by adding custom classes. See [\_helpers.scss](sass/base/_helpers) to view the currently available class names.

#### Icons
Icons are a collection of custom icons, Bootstrap-Glyphicons and Font Awesome.
New icons can be added with Font Awesome since the complete collection is loaded into the pages: http://fontawesome.io/cheatsheet/

#### Features

##### Table of contents

A table of contents can be generated (see [examples/inpage_nav_left.html](examples/inpage_nav_left.html)). This features uses the [tocbot.js library](https://tscanlin.github.io/tocbot/). The JavaScript code to enable this effect is present in [main.js](js/main.js).

To enable, add a navigation block to your page.

    <aside class="col-sm-3 inpage-nav-container xs-hide">
        <div id="nav-anchor"></div>
        <nav role="navigation" data-lockfixed="true" class="padding-default bg-tertiary">
            <h2>On this page</h2>
            <div id="toc"></div>
        </nav>
    </aside>

Make sure your page content is inside #content-core block.

##### Editor CSS

A stripped-down css is generated for online editors (ckeditor/tinemce). An example can be found [here](examples/editor-ckeditor.html). 