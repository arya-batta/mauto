Le.contentVersions = {};
Le.versionNamespace = '';
Le.currentContentVersion = -1;

/**
 * Setup versioning for the given namespace
 *
 * @param undoCallback function
 * @param redoCallback function
 * @param namespace
 */
Le.prepareVersioning = function (undoCallback, redoCallback, namespace) {
    // Check if localStorage is supported and if not, disable undo/redo buttons
    if (!Le.isLocalStorageSupported()) {
        mQuery('.btn-undo').prop('disabled', true);
        mQuery('.btn-redo').prop('disabled', true);

        return;
    }

    mQuery('.btn-undo')
        .prop('disabled', false)
        .on('click', function() {
            Le.undoVersion(undoCallback);
        });

    mQuery('.btn-redo')
        .prop('disabled', false)
        .on('click', function() {
            Le.redoVersion(redoCallback);
        });

    Le.currentContentVersion = -1;

    if (!namespace) {
        namespace = window.location.href;
    }

    if (typeof Le.contentVersions[namespace] == 'undefined') {
        Le.contentVersions[namespace] = [];
    }

    Le.versionNamespace = namespace;

    console.log(namespace);
};

/**
 * Clear versioning
 *
 * @param namespace
 */
Le.clearVersioning = function () {
    if (!Le.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (typeof Le.contentVersions[Le.versionNamespace] !== 'undefined') {
        delete Le.contentVersions[Le.versionNamespace];
    }

    Le.versionNamespace = '';
    Le.currentContentVersion = -1;
};

/**
 * Store a version
 *
 * @param content
 */
Le.storeVersion = function(content) {
    if (!Le.versionNamespace) {
        throw 'Versioning not configured';
    }

    // Store the content
    Le.contentVersions[Le.versionNamespace].push(content);

    // Set the current location to the latest spot
    Le.currentContentVersion = Le.contentVersions[Le.versionNamespace].length;
};

/**
 * Decrement a version
 *
 * @param callback
 */
Le.undoVersion = function(callback) {
    console.log('undo');
    if (!Le.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (Le.currentContentVersion < 0) {
        // Nothing to undo

        return;
    }

    var version = Le.currentContentVersion - 1;
    if (Le.getVersion(version, callback)) {
        --Le.currentContentVersion;
    };
};

/**
 * Increment a version
 *
 * @param callback
 */
Le.redoVersion = function(callback) {
    console.log('redo');
    if (!Le.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (Le.currentContentVersion < 0 || Le.contentVersions[Le.versionNamespace].length === Le.currentContentVersion) {
        // Nothing to redo

        return;
    }

    var version = Le.currentContentVersion + 1;
    if (Le.getVersion(version, callback)) {
        ++Le.currentContentVersion;
    };
};

/**
 * Check for a given version and execute callback
 *
 * @param version
 * @param command
 * @returns {boolean}
 */
Le.getVersion = function(version, callback) {
    var content = false;
    if (typeof Le.contentVersions[Le.versionNamespace][version] !== 'undefined') {
        content = Le.contentVersions[Le.versionNamespace][version];
    }

    if (false !== content && typeof callback == 'function') {
        callback(content);

        return true;
    }

    return false;
};