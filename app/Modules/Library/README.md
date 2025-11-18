# Library Module

The Library module manages digitised resources with Google Drive (or equivalent)
sync and QR labelling. It enables rapid discovery of per-class materials while
keeping drive sharing policies under governance.

## Capabilities

- Register documents with metadata and synchronise them to Drive storage.
- Automatically issue QR codes for physical or printed artefacts.
- Record every registration event to the append-only audit log.

## Key Services

- `Services\DocumentCatalog` – orchestrates Drive uploads, QR issuance, and
audit logging.
- `Services\DriveAdapterInterface` – contract for integrating Drive or any
  storage backend capable of returning shareable links.

## Routing

- `POST /library/documents` – register a new document and receive QR + Drive
  metadata in the response.

Provide a concrete implementation of `libraryDriveAdapter` in the service
container to enable runtime uploads.
