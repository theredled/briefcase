import {useRoute} from "ziggy-js";
import {Head} from "@inertiajs/react";


export default function DownloadsIndex (props: any) {
    const route = useRoute();

    const documents = props.documents;

    return <>
        <Head title="Téléchargements" />
        <section className="section-block page-section" id="downloads">
        <div className="section-h2">
            <h2>
                Téléchargements
            </h2>
        </div>
        <div className="section-content">
            <ul>
                {documents.map((doc: any) =>
                <li className="file-item">
                    <a href={ route('download.download', {'documentToken': doc.token, 'inline': 1}) }>
                        <i className="far {{ $doc->faCssClass }} icon"></i>
                        <span className="title">{doc.title}</span>
                    </a>
                </li>
                )}
            </ul>
            <p className="text-block">Pour obtenir les fichiers sons, contacter
                <em>ftiymusic [at] gmail.com</em>
            </p>
        </div>
        </section>
    </>
}
